<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Public landing endpoint describing the API. Kept as an invokable controller
 * (instead of a route closure) so routes remain cacheable.
 */
final class ApiInfoController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'description' => 'GIPHY integration REST API (Hexagonal Architecture + DDD).',
            'documentation' => 'See README.md and the Postman collection under /docs.',
            'playground' => url('/playground.html'),
            'health' => url('/up'),
            'services' => [
                'login' => 'POST /api/login',
                'search_gifs' => 'GET /api/gifs/search',
                'get_gif' => 'GET /api/gifs/{id}',
                'save_favorite' => 'POST /api/favorites',
            ],
        ]);
    }
}
