<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'description' => 'GIPHY integration REST API (Hexagonal Architecture + DDD).',
    'docs' => 'See README.md and the Postman collection under /docs.',
    'health' => url('/up'),
]));
