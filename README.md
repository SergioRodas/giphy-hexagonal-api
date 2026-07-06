# GIPHY Hexagonal API

A REST API that integrates with the [GIPHY](https://developers.giphy.com/) API and
exposes four authenticated services, built with **Laravel 12**, **Hexagonal
Architecture + DDD** and **OAuth2** (Laravel Passport).

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![OAuth2](https://img.shields.io/badge/Auth-OAuth2%20(Passport)-46A2F1)
![Database](https://img.shields.io/badge/DB-MariaDB%2011-003545?logo=mariadb&logoColor=white)
![Tests](https://img.shields.io/badge/tests-48%20passing-2ea44f)

---

## Table of contents

- [Overview](#overview)
- [Tech stack](#tech-stack)
- [Architecture](#architecture)
- [Project structure](#project-structure)
- [Getting started (Docker)](#getting-started-docker)
- [API reference](#api-reference)
- [Interaction audit log](#interaction-audit-log)
- [Testing](#testing)
- [Diagrams](#diagrams)
- [Postman collection](#postman-collection)
- [Configuration](#configuration)
- [Design decisions & deviations](#design-decisions--deviations)

---

## Overview

The API exposes four services. Every service except **Login** requires a valid
OAuth2 access token, and **every** interaction is persisted as an audit log.

| Service | Method & path | Description |
| --- | --- | --- |
| **Login** | `POST /api/login` | Authenticate with e-mail/password → OAuth2 token (30-minute expiry). |
| **Search GIFs** | `GET /api/gifs/search` | Search GIFs by phrase/term with `query`, `limit`, `offset`. |
| **Get GIF by ID** | `GET /api/gifs/{id}` | Get the information of a single GIF. |
| **Save Favorite GIF** | `POST /api/favorites` | Store a favorite GIF (`gif_id`, `alias`, `user_id`) for a user. |

---

## Tech stack

- **PHP 8.3** · **Laravel 12** (the brief asks for "v11 or higher"; see
  [design decisions](#design-decisions--deviations) for why v12).
- **Laravel Passport** — OAuth2 authorization server (Bearer access tokens).
- **MariaDB 11** (MySQL-compatible).
- **Docker / Docker Compose** — PHP-FPM + Nginx + MariaDB.
- **PHPUnit 11** — unit + feature tests.

---

## Architecture

The project follows **Hexagonal Architecture (Ports & Adapters) + DDD** with a
strict dependency rule: **dependencies always point inwards**
(`Infrastructure → Application → Domain`). The domain has zero framework
dependencies.

```
             HTTP / Console (adapters)
                      │
        ┌─────────────▼──────────────┐
        │       Infrastructure        │  Controllers, Eloquent, GIPHY HTTP,
        │   (adapters, framework)     │  Passport, middleware, providers
        └─────────────┬──────────────┘
                      │ implements ports
        ┌─────────────▼──────────────┐
        │        Application           │  Use cases (orchestration)
        │       (use cases)            │  Commands / Queries (DTOs)
        └─────────────┬──────────────┘
                      │ depends on
        ┌─────────────▼──────────────┐
        │          Domain              │  Entities, Value Objects, Ports
        │   (pure business logic)      │  (Repository/Service interfaces)
        └─────────────────────────────┘
```

- **Domain** (`src/Domain`) — Entities (`User`, `Gif`, `Favorite`, `RequestLog`),
  Value Objects (`Email`, `GifId`, `Alias`, `SearchCriteria`, `AuthToken`, …),
  domain exceptions and **ports** (interfaces). No Laravel, no Eloquent.
- **Application** (`src/Application`) — one use case per service. Each receives a
  `Command`/`Query` DTO and orchestrates the domain through ports. This is where
  the business rules live and where isolated unit tests are focused.
- **Infrastructure** (`src/Infrastructure`) — the adapters that implement the
  ports: Eloquent repositories, the GIPHY HTTP client, Passport token issuing,
  HTTP controllers/requests/resources and the audit middleware.

**Persistence is only ever reached through domain ports** — controllers never
touch Eloquent directly. The single place where ports are bound to adapters is
`Infrastructure\Providers\DomainServiceProvider` (the composition root).

| Port (Domain) | Adapter (Infrastructure) |
| --- | --- |
| `User\Repository\UserRepository` | `Eloquent\EloquentUserRepository` |
| `Favorite\Repository\FavoriteRepository` | `Eloquent\EloquentFavoriteRepository` |
| `Gif\Repository\GifRepository` | `Giphy\GiphyGifRepository` |
| `Audit\Repository\RequestLogRepository` | `Eloquent\EloquentRequestLogRepository` |
| `Auth\Contract\TokenIssuer` | `Auth\PassportTokenIssuer` |
| `Auth\Contract\PasswordHasher` | `Auth\LaravelPasswordHasher` |

Only framework bootstrap lives in `app/`; **all application logic is under
`src/`** (autoloaded via the `Domain\`, `Application\`, `Infrastructure\`
PSR-4 namespaces).

---

## Project structure

```
src/
├── Domain/                      # Pure business logic (no framework)
│   ├── Shared/                  # Email VO, DomainException base
│   ├── User/                    # User entity, UserId, UserRepository port
│   ├── Auth/                    # AuthToken, TokenIssuer & PasswordHasher ports
│   ├── Gif/                     # Gif entity, VOs, GifRepository port
│   ├── Favorite/                # Favorite entity, Alias, FavoriteRepository port
│   └── Audit/                   # RequestLog entity, RequestLogRepository port
├── Application/                 # Use cases + Command/Query DTOs
│   ├── Auth/Login/
│   ├── Gif/Search/  ·  Gif/Show/
│   └── Favorite/Save/
└── Infrastructure/              # Adapters
    ├── Auth/                    # PassportTokenIssuer, LaravelPasswordHasher
    ├── Giphy/                   # GiphyGifRepository (+ payload mapper)
    ├── Persistence/Eloquent/    # Models + Eloquent repositories
    ├── Http/                    # Controllers, Requests, Resources, Middleware
    └── Providers/               # DomainServiceProvider (composition root)

docs/                            # DIAGRAMS.md, uml/ (PlantUML), postman/
tests/                           # Unit/ (isolated) + Feature/ (HTTP) + Support/
```

---

## Getting started (Docker)

### Prerequisites

- Docker + Docker Compose
- A free GIPHY API key — <https://developers.giphy.com/dashboard/>

### Steps

```bash
# 1. Clone
git clone https://github.com/SergioRodas/giphy-hexagonal-api.git
cd giphy-hexagonal-api

# 2. Create the environment file and set your GIPHY key
cp .env.example .env
#   edit .env  ->  GIPHY_API_KEY=your_key_here
#   (APP_KEY is generated automatically on first boot)

# 3. Build and start the stack (app + nginx + mariadb)
docker compose up -d --build
```

On first boot the app container automatically waits for the database, runs the
migrations, generates the Passport encryption keys, seeds a demo user and a
personal-access client, and caches config/routes. **The first startup takes
~30–60s** (RSA key generation + migrations); a brief `502` from Nginx during
that window is expected — just retry.

The API is then available at **<http://localhost:8080>**.

```bash
# Health check
curl http://localhost:8080/up

# Log in with the seeded demo user
curl -X POST http://localhost:8080/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"demo@giphy-hexagonal.test","password":"password"}'
```

Or open the browser **playground** at <http://localhost:8080/playground.html> to
log in and exercise all four services interactively (it renders the GIFs too).

**Seeded demo account:** `demo@giphy-hexagonal.test` / `password` (id `1`).

Useful commands:

```bash
docker compose logs -f app                  # follow app logs
docker compose exec app php artisan test    # run the test suite (see Testing)
docker compose down                         # stop
docker compose down -v                      # stop and wipe the database volume
```

---

## API reference

Base URL: `http://localhost:8080`. All responses are JSON. Authenticated
endpoints expect an `Authorization: Bearer <access_token>` header.

### 1. Login — `POST /api/login`

Request:

```json
{ "email": "demo@giphy-hexagonal.test", "password": "password" }
```

Response `200 OK`:

```json
{
  "token_type": "Bearer",
  "access_token": "eyJ0eXAiOiJKV1Qi...",
  "expires_in": 1800,
  "expires_at": "2026-07-06T20:30:00+00:00"
}
```

- `401 Unauthorized` — invalid credentials · `422` — validation error.

### 2. Search GIFs — `GET /api/gifs/search`

Query parameters: `query` *(required, string)*, `limit` *(optional, 1–50,
default 25)*, `offset` *(optional, ≥ 0, default 0)*.

```
GET /api/gifs/search?query=cat&limit=2&offset=0
```

Response `200 OK`:

```json
{
  "data": [
    {
      "id": "Ev477g37MJORyOWfdG",
      "title": "Cat Meme GIF",
      "url": "https://giphy.com/gifs/Ev477g37MJORyOWfdG",
      "rating": "g",
      "username": "byomid",
      "images": {
        "original": { "url": "https://media.giphy.com/.../giphy.gif", "width": 480, "height": 476 },
        "preview":  { "url": "https://media.giphy.com/.../small.gif", "width": 100, "height": 99 }
      }
    }
  ],
  "pagination": { "total_count": 500, "count": 1, "offset": 0 }
}
```

- `401` — missing/invalid token · `422` — missing `query` · `502` — GIPHY unavailable.

### 3. Get GIF by ID — `GET /api/gifs/{id}`

```
GET /api/gifs/Ev477g37MJORyOWfdG
```

Response `200 OK`: `{ "data": { ...same shape as a search item... } }`

- `401` — missing/invalid token · `404` — GIF not found · `502` — GIPHY unavailable.

### 4. Save Favorite GIF — `POST /api/favorites`

Request:

```json
{ "gif_id": "Ev477g37MJORyOWfdG", "alias": "My favourite cat", "user_id": 1 }
```

Response `201 Created`:

```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "gif_id": "Ev477g37MJORyOWfdG",
    "alias": "My favourite cat",
    "created_at": "2026-07-06T20:00:00+00:00"
  }
}
```

`user_id` must match the authenticated user (it is accepted per the brief but
bound to the token — see [design decisions](#design-decisions--deviations)).

- `401` — missing/invalid token
- `403 Forbidden` — `user_id` does not match the authenticated user
- `409 Conflict` — the user already saved this GIF (unique `user_id + gif_id`)
- `422` — validation error

Error responses share a consistent shape:

```json
{ "error": "favorite_already_exists", "message": "User 1 has already saved GIF ..." }
```

---

## Interaction audit log

Every request to the API is persisted in the `request_logs` table via the
`LogInteraction` middleware, capturing exactly the six data points required:

| Column | Meaning |
| --- | --- |
| `user_id` | the authenticated user (null for Login / unauthenticated calls) |
| `service` | the service consulted (route name, e.g. `gifs.search`) |
| `request_body` | the request payload — **`password` is redacted** |
| `status_code` | the HTTP response status |
| `response_body` | the response payload — **issued tokens (`access_token`) are redacted** |
| `ip_address` | the origin IP |

The log is written on `terminate()`, so it also records error responses
(401/404/409/422/502). Inspect it with:

```bash
docker compose exec db mariadb -ugiphy -psecret giphy \
  -e "SELECT id, user_id, service, method, status_code, ip_address FROM request_logs;"
```

---

## Testing

48 tests (113 assertions) run against an in-memory SQLite database:

```bash
docker compose exec app php artisan config:clear   # tests use the sqlite override
docker compose exec app php artisan test
```

- **Unit tests** (`tests/Unit`) exercise the domain value objects and **every use
  case in isolation**, using hand-rolled in-memory fakes of the ports — no
  framework, no database. This is the "business logic tested independently of the
  implementation details" the brief asks for.
- **Feature tests** (`tests/Feature`) drive the real HTTP endpoints with the GIPHY
  API mocked via `Http::fake()`, asserting status codes, payloads, the 30-minute
  token, favorites rules and audit-log persistence with password redaction.

---

## Diagrams

See **[docs/DIAGRAMS.md](docs/DIAGRAMS.md)** — rendered directly on GitHub
(Mermaid) — for:

- **Use Case Diagram**
- **Sequence Diagrams** (one per use case)
- **Entity–Relationship Diagram (DER)**

Formal **UML / PlantUML** sources are in [docs/uml/](docs/uml/).

---

## Postman collection

Import both files from [docs/postman/](docs/postman/):

- `GIPHY-Hexagonal-API.postman_collection.json`
- `GIPHY-Hexagonal-API.postman_environment.json`

Select the *GIPHY Hexagonal API - Local* environment and run **Login** first: a
test script stores the returned token in the `access_token` environment variable,
and every other request inherits collection-level Bearer auth automatically.

---

## Configuration

Key environment variables (see [.env.example](.env.example)):

| Variable | Default | Description |
| --- | --- | --- |
| `GIPHY_API_KEY` | *(empty)* | **Required.** Your GIPHY API key. |
| `GIPHY_BASE_URL` | `https://api.giphy.com/v1` | GIPHY API base URL. |
| `GIPHY_TIMEOUT` | `10` | HTTP timeout (seconds) for GIPHY calls. |
| `ACCESS_TOKEN_TTL_MINUTES` | `30` | OAuth2 access-token lifetime. |
| `DB_*` | `db` / `giphy` / `giphy` / `secret` | MariaDB connection (matches compose). |
| `APP_PORT` | `8080` | Host port Nginx is published on. |

---

## Design decisions & deviations

- **Laravel 12, not 11.** The brief allows "Laravel v11 **or higher**". Three
  security advisories affecting the entire Laravel 11.x line (signed-URL path
  confusion, CRLF injection in the e-mail rule) are only fixed in Laravel
  12.60/12.61.1, so the project targets `^12.61.1` to ship free of known
  vulnerabilities without disabling Composer's advisory checks.
- **GIF ids are alphanumeric, not numeric.** The brief describes `ID`/`GIF_ID` as
  "numeric", but GIPHY actually issues alphanumeric identifiers (e.g.
  `Ev477g37MJORyOWfdG`). The real provider contract is honoured and `gif_id` is
  validated as `alpha_num`.
- **OAuth2.** Passport is the OAuth2 authorization server. Login verifies
  credentials in the application layer (a testable domain rule) and then issues a
  Bearer access token through the OAuth2 server; protected routes use the
  `auth:api` (Passport) guard. Tokens expire in 30 minutes.
- **`user_id` in the favorites body.** Accepted as input per the brief, but bound
  to the authenticated principal: a request whose `user_id` differs from the
  token's user is rejected with `403`, preventing cross-account writes (IDOR).
- **`config.platform.php = 8.3`** pins Composer's resolver to the runtime PHP so
  the lock file is reproducible against the Docker image.

### Security hardening

Applied after an adversarial self-review of the codebase:

- OAuth2 Bearer auth on every service; login (`10/min`) and the authenticated
  group (`60/min`) are rate-limited against brute force / abuse.
- `password` (request) and issued `access_token` (response) are redacted from the
  audit log.
- Favorites are bound to the authenticated user (no IDOR).
- The favorites unique constraint is enforced atomically (concurrent duplicate →
  `409`, not `500`).
- HTTP status mapping lives in the infrastructure layer, keeping the domain free
  of transport concerns.
