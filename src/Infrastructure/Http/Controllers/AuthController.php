<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Application\Auth\Login\LoginCommand;
use Application\Auth\Login\LoginUseCase;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Infrastructure\Http\Requests\LoginRequest;

final class AuthController
{
    /**
     * POST /api/login — authenticate and issue an OAuth2 access token.
     */
    public function login(LoginRequest $request, LoginUseCase $useCase): JsonResponse
    {
        $token = $useCase->execute(new LoginCommand(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        ));

        return response()->json([
            'token_type' => $token->tokenType(),
            'access_token' => $token->accessToken(),
            'expires_in' => $token->expiresIn(new DateTimeImmutable),
            'expires_at' => $token->expiresAt()->format(DATE_ATOM),
        ]);
    }
}
