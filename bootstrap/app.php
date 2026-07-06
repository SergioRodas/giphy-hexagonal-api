<?php

use Domain\Shared\Exception\DomainException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API clients always get a JSON error body.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Domain rule violations carry their own HTTP status.
        $exceptions->render(function (DomainException $e, Request $request) {
            return response()->json([
                'error' => $e->errorCode(),
                'message' => $e->getMessage(),
            ], $e->statusCode());
        });

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
