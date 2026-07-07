# Diagrams

All diagrams below are written in [Mermaid](https://mermaid.js.org/) and render
automatically on GitHub. The equivalent formal **UML (PlantUML)** sources live in
[`docs/uml/`](uml/) for anyone who prefers to render them with a UML tool.

---

## 1. Use Case Diagram

```mermaid
graph LR
    client([API Client]):::actor
    giphy([GIPHY API]):::actor

    subgraph API["GIPHY Hexagonal API"]
        UC1(("Login"))
        UC2(("Search GIFs"))
        UC3(("Get GIF by ID"))
        UC4(("Save Favorite GIF"))
        AUTH(("Authenticate<br/>OAuth2 Bearer"))
        LOG(("Persist<br/>Interaction Log"))
    end

    client --> UC1
    client --> UC2
    client --> UC3
    client --> UC4

    UC2 -. include .-> AUTH
    UC3 -. include .-> AUTH
    UC4 -. include .-> AUTH

    UC1 -. include .-> LOG
    UC2 -. include .-> LOG
    UC3 -. include .-> LOG
    UC4 -. include .-> LOG

    UC2 --> giphy
    UC3 --> giphy

    classDef actor fill:#2563eb,stroke:#1e40af,color:#fff;
```

- **Login** is public; it exchanges e-mail/password for a 30-minute OAuth2 token.
- **Search GIFs**, **Get GIF by ID** and **Save Favorite GIF** require a valid token
  (`«include» Authenticate`).
- Every service `«include»`s **Persist Interaction Log** (the audit requirement).
- Search / Get query the external **GIPHY API**.

---

## 2. Sequence Diagrams

### 2.1 Login

```mermaid
sequenceDiagram
    actor C as API Client
    participant Ctrl as AuthController
    participant UC as LoginUseCase
    participant UR as UserRepository«port»
    participant PH as PasswordHasher«port»
    participant TI as TokenIssuer«port»
    participant DB as MariaDB
    participant LOG as LogInteraction (terminate)

    C->>Ctrl: POST /api/login {email, password}
    Note over Ctrl: LoginRequest validates input (422 on failure)
    Ctrl->>UC: execute(LoginCommand)
    UC->>UR: findByEmail(Email)
    UR->>DB: SELECT * FROM users
    DB-->>UR: user row
    UR-->>UC: User (or null)
    UC->>PH: verify(password, hashedPassword)
    PH-->>UC: true / false
    alt invalid credentials
        UC-->>Ctrl: throw InvalidCredentials
        Ctrl-->>C: 401 {error: invalid_credentials}
    else valid
        UC->>TI: issueFor(User)
        TI->>DB: persist OAuth2 access token (Passport)
        TI-->>UC: AuthToken (expires in 30 min)
        UC-->>Ctrl: AuthToken
        Ctrl-->>C: 200 {access_token, expires_in, expires_at}
    end
    Ctrl-->>LOG: response sent
    LOG->>DB: INSERT request_logs (password REDACTED)
```

### 2.2 Search GIFs

```mermaid
sequenceDiagram
    actor C as API Client
    participant Ctrl as GifController
    participant UC as SearchGifsUseCase
    participant GR as GifRepository«port»
    participant GX as GiphyGifRepository«adapter»
    participant G as GIPHY API
    participant DB as MariaDB
    participant LOG as LogInteraction (terminate)

    C->>Ctrl: GET /api/gifs/search?query&limit&offset (Bearer token)
    Note over Ctrl: auth:api guard + SearchGifsRequest (401 / 422)
    Ctrl->>UC: execute(SearchGifsQuery)
    UC->>GR: search(SearchCriteria)
    GR->>GX: search(SearchCriteria)
    GX->>G: GET /v1/gifs/search
    G-->>GX: JSON payload
    GX-->>UC: GifSearchResult (mapped to domain)
    UC-->>Ctrl: GifSearchResult
    Ctrl-->>C: 200 {data[], pagination}
    Ctrl-->>LOG: response sent
    LOG->>DB: INSERT request_logs
```

### 2.3 Get GIF by ID

```mermaid
sequenceDiagram
    actor C as API Client
    participant Ctrl as GifController
    participant UC as GetGifByIdUseCase
    participant GR as GifRepository«port»
    participant GX as GiphyGifRepository«adapter»
    participant G as GIPHY API
    participant DB as MariaDB
    participant LOG as LogInteraction (terminate)

    C->>Ctrl: GET /api/gifs/{id} (Bearer token)
    Ctrl->>UC: execute(GetGifByIdQuery)
    UC->>GR: findById(GifId)
    GR->>GX: findById(GifId)
    GX->>G: GET /v1/gifs/{id}
    alt found
        G-->>GX: 200 JSON
        GX-->>UC: Gif
        UC-->>Ctrl: Gif
        Ctrl-->>C: 200 {data}
    else not found
        G-->>GX: 404
        GX-->>UC: throw GifNotFound
        UC-->>Ctrl: GifNotFound
        Ctrl-->>C: 404 {error: gif_not_found}
    end
    Ctrl-->>LOG: response sent
    LOG->>DB: INSERT request_logs
```

### 2.4 Save Favorite GIF

```mermaid
sequenceDiagram
    actor C as API Client
    participant Ctrl as FavoriteController
    participant UC as SaveFavoriteUseCase
    participant UR as UserRepository«port»
    participant FR as FavoriteRepository«port»
    participant DB as MariaDB
    participant LOG as LogInteraction (terminate)

    C->>Ctrl: POST /api/favorites {gif_id, alias, user_id} (Bearer token)
    Note over Ctrl: auth:api + SaveFavoriteRequest (401 / 422)
    Ctrl->>UC: execute(SaveFavoriteCommand incl. authenticated user id)
    alt user_id ≠ authenticated user (ownership rule)
        UC-->>Ctrl: throw FavoriteOwnershipViolation
        Ctrl-->>C: 403 {error: favorite_ownership_violation}
    else owner matches
        UC->>UR: exists(UserId)
        UR->>DB: SELECT 1 FROM users
        alt user missing
            UC-->>Ctrl: throw UserNotFound
            Ctrl-->>C: 422 {error: user_not_found}
        else user exists
            UC->>FR: existsForUserAndGif(UserId, GifId)
            FR->>DB: SELECT 1 FROM favorites
            alt duplicate
                UC-->>Ctrl: throw FavoriteAlreadyExists
                Ctrl-->>C: 409 {error: favorite_already_exists}
            else new
                UC->>FR: save(Favorite)
                FR->>DB: INSERT favorites
                FR-->>UC: Favorite (with id)
                UC-->>Ctrl: Favorite
                Ctrl-->>C: 201 {data}
            end
        end
    end
    Ctrl-->>LOG: response sent
    LOG->>DB: INSERT request_logs
```

> The ownership rule (a token holder may only save favorites for their own
> account) lives in `SaveFavoriteUseCase`, so it applies to any adapter that
> invokes the use case — not just the HTTP controller.

---

## 3. Entity–Relationship Diagram (DER)

```mermaid
erDiagram
    users ||--o{ favorites : "saves"
    users ||--o{ request_logs : "performs"
    users ||--o{ oauth_access_tokens : "owns"
    oauth_clients ||--o{ oauth_access_tokens : "issues"
    oauth_access_tokens ||--o| oauth_refresh_tokens : "refreshed by"

    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        timestamps created_updated
    }

    favorites {
        bigint id PK
        bigint user_id FK
        string gif_id
        string alias
        timestamps created_updated
    }

    request_logs {
        bigint id PK
        bigint user_id FK "nullable"
        string service
        string method
        string path
        json request_body
        smallint status_code
        longtext response_body
        string ip_address
        timestamp created_at
    }

    oauth_clients {
        uuid id PK
        string owner_type "nullable"
        bigint owner_id "nullable"
        string name
        string secret "nullable"
        string provider "nullable"
        text redirect_uris
        text grant_types
        boolean revoked
        timestamps created_updated
    }

    oauth_access_tokens {
        string id PK
        bigint user_id "nullable"
        uuid client_id FK
        string name "nullable"
        text scopes
        boolean revoked
        timestamp expires_at
    }

    oauth_refresh_tokens {
        string id PK
        string access_token_id FK
        boolean revoked
        timestamp expires_at
    }
```

> `favorites` has a **unique constraint on `(user_id, gif_id)`** enforcing that a
> user cannot save the same GIF twice. `request_logs` is the audit trail that
> stores, for every request: the user, the service, the request body (with
> secrets redacted), the HTTP status, the response body and the origin IP.
> Passport also creates `oauth_auth_codes` and `oauth_device_codes` (omitted here
> for clarity as they are unused by this API).
