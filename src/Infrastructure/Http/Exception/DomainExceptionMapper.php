<?php

declare(strict_types=1);

namespace Infrastructure\Http\Exception;

use Domain\Auth\Exception\InvalidCredentials;
use Domain\Favorite\Exception\FavoriteAlreadyExists;
use Domain\Favorite\Exception\FavoriteOwnershipViolation;
use Domain\Gif\Exception\GifNotFound;
use Domain\Gif\Exception\GifProviderUnavailable;
use Domain\Shared\Exception\DomainException;
use Domain\User\Exception\UserNotFound;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Translates domain exceptions into HTTP responses. This is the single place
 * where domain outcomes are mapped to transport (HTTP) semantics, keeping that
 * concern out of the domain layer.
 */
final class DomainExceptionMapper
{
    public function toResponse(DomainException $exception): JsonResponse
    {
        return response()->json([
            'error' => $this->errorCode($exception),
            'message' => $exception->getMessage(),
        ], $this->statusCode($exception));
    }

    public function statusCode(DomainException $exception): int
    {
        return match (true) {
            $exception instanceof InvalidCredentials => Response::HTTP_UNAUTHORIZED,          // 401
            $exception instanceof FavoriteOwnershipViolation => Response::HTTP_FORBIDDEN,      // 403
            $exception instanceof GifNotFound => Response::HTTP_NOT_FOUND,                     // 404
            $exception instanceof FavoriteAlreadyExists => Response::HTTP_CONFLICT,            // 409
            $exception instanceof GifProviderUnavailable => Response::HTTP_BAD_GATEWAY,        // 502
            $exception instanceof UserNotFound => Response::HTTP_UNPROCESSABLE_ENTITY,         // 422
            default => Response::HTTP_UNPROCESSABLE_ENTITY,                                    // 422
        };
    }

    /**
     * Stable, machine-readable error code derived from the exception class name
     * (e.g. GifNotFound -> "gif_not_found").
     */
    public function errorCode(DomainException $exception): string
    {
        $parts = explode('\\', $exception::class);
        $shortName = end($parts);

        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
    }
}
