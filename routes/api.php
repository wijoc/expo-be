<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\StoreCategoryController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

/** Public Route */
    Route::group(['prefix' => 'beta'], function () {
        // User Routes
        Route::post('/login', [UserController::class, 'login']);
        Route::get('/user-register', [UserController::class, 'userRegister']);
        Route::get('/refresh-token', [UserController::class, 'refreshToken']);

        // Store Routes
        Route::get('/stores', [StoreController::class, 'index']);
        Route::get('/stores/{store}', [StoreController::class, 'show']);

        // Product Routes
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);

        // Categories Routes
        Route::get('/categories/product', [ProductCategoryController::class, 'index']);
        Route::get('/categories/store', [StoreCategoryController::class, 'index']);
    });

/** Protected Route */
  // User Protected Routes
    Route::group(['prefix' => 'beta', 'controller' => UserController::class], function () {
        Route::get('/users', 'index')->middleware('jauth:su|admin');
        Route::post('/users', 'store')->middleware('jauth:su|admin');
        Route::get('/my-profile', 'userProfile')->middleware('jauth');
        Route::post('/refresh-access-token', 'refreshToken')->middleware('jauth');
    });

  // Store Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth', 'controller' => StoreController::class], function () {
        Route::post('/stores', 'store');
        Route::put('/stores/{id}', 'update');
        Route::delete('/stores/{id}', 'destroy');
    });

  // Product Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth', 'controller' => ProductController::class], function () {
        Route::post('/products', 'store');
        Route::put('/products/{id}', 'update');
        Route::delete('/products/{id}', 'destroy');
    });

  // Category Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth:su,admin', 'controller' => ProductCategoryController::class], function () {
        Route::post('/categories/product', 'store');
        Route::put('/categories/product/{id}', 'update');
        Route::delete('/categories/product/{id}', 'destroy');
    });
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth:su,admin', 'controller' => StoreCategoryController::class], function () {
        Route::post('/categories/store', 'store');
        Route::put('/categories/store/{id}', 'update');
        Route::delete('/categories/store/{id}', 'destroy');
    });
