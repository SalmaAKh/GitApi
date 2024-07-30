<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\SwaggerController;
use Illuminate\Support\Facades\Route;

Route::get('/docs', [SwaggerController::class, 'docs']);

Route::get('/repositories', [RepositoryController::class, 'index']);
Route::post('/repositories/email', [RepositoryController::class, 'sendMail']);
