<?php

use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecentWinningsController;
use App\Http\Controllers\Api\VipPlanController;
use App\Http\Controllers\Api\VipResultController;
use App\Http\Controllers\Api\MatchController;
use Illuminate\Support\Facades\Route;

Route::get('/predictions', [PredictionController::class, 'index']);
Route::get('/predictions/{date}', [PredictionController::class, 'byDate'])->where('date', '\d{4}-\d{2}-\d{2}');
Route::get('/predictions/live', [PredictionController::class, 'live']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/categories/{slug}/{date}', [CategoryController::class, 'byDate'])->where('date', '\d{4}-\d{2}-\d{2}');

Route::get('/fixtures/upcoming', [FixtureController::class, 'upcoming']);

Route::get('/recent-winnings', [RecentWinningsController::class, 'index']);

Route::get('/vip/plans', [VipPlanController::class, 'index']);
Route::get('/vip-results', [VipResultController::class, 'index']);

Route::get('/matches/{slug}', [MatchController::class, 'show']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);
Route::post('/articles', [ArticleController::class, 'store']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user()->only(['id', 'name', 'email']);
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
