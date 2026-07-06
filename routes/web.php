<?php

use Illuminate\Support\Facades\Route;
use Infrastructure\Http\Controllers\ApiInfoController;

Route::get('/', ApiInfoController::class);
