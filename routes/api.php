<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('verify-mfa', [AuthController::class, 'verifyMfa']);


Route::middleware(['auth.jwt'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->middleware('admin');
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/', [ProductController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('admin');
    });
    
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::patch('/{id}/status', [OrderController::class, 'updateStatus'])->middleware('admin');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('admin');
        Route::post('/', [UserController::class, 'store'])->middleware('admin');
        Route::get('{id}', [UserController::class, 'show'])->middleware('admin');
        Route::put('{id}', [UserController::class, 'update'])->middleware('admin');
        Route::delete('{id}', [UserController::class, 'destroy'])->middleware('admin');
    });
});
