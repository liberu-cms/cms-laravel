<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ContentCategoryController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Content endpoints
    Route::apiResource('contents', ContentController::class);
    Route::get('/contents/{content}/analytics', [ContentController::class, 'analytics']);
    Route::post('/contents/{content}/view', [ContentController::class, 'recordView']);

    // Content categories
    Route::apiResource('categories', ContentCategoryController::class);

    // Workflow actions
    Route::post('/contents/{content}/submit-for-review', [ContentController::class, 'submitForReview']);
    Route::post('/contents/{content}/approve', [ContentController::class, 'approve']);
    Route::post('/contents/{content}/reject', [ContentController::class, 'reject']);
    Route::post('/contents/{content}/publish', [ContentController::class, 'publish']);
    Route::post('/contents/{content}/schedule', [ContentController::class, 'schedule']);
});