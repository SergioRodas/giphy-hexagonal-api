<?php

use Domain\Shared\Exception\DomainException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Infrastructure\Http\Exception\DomainExceptionMapper;
use Infrastructure\Http\Middleware\LogInteraction;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Record every interaction with the API (audit trail requirement).
        // Registered globally (not just on the api group) so that requests which
        // never match a route - unknown paths, wrong verbs - are audited too; the
        // middleware itself scopes logging to /api/* on terminate().
        $middleware->append(LogInteraction::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API clients always get a JSON error body.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Domain rule violations are mapped to HTTP by the infrastructure layer.
        $exceptions->render(
            fn (DomainException $e) => (new DomainExceptionMapper)->toResponse($e)
        );

        // Value-object guards throw InvalidArgumentException -> 422 for the API.
        $exceptions->render(function (InvalidArgumentException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'invalid_argument',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return null;
        });
    })->create();
