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
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/** Store Route */
Route::group(['prefix' => 'api', 'controller' => StoreController::class], function () {
    Route::post('/stores', 'store');
    Route::get('/stores', 'index');
    Route::get('/stores/{slug}', 'show');
    Route::put('/stores/{id}', 'update');
    Route::delete('/stores/{id}', 'destroy');
});

/** User Route */
Route::group(['prefix' => 'api', 'controller' => UserController::class], function () {
    Route::get('/users', 'index');
    Route::post('/users', 'store');
});

/** Product Route */
Route::group(['prefix' => 'api', 'controller' => ProductController::class], function () {
    Route::post('/products', 'store');
    Route::get('/products', 'index');
    Route::get('/products/{slug}', 'show');
    Route::put('/products/{id}', 'update');
    Route::delete('/products/{id}', 'destroy');
});

/** Category Route */
Route::group(['prefix' => 'api', 'controller' => ProductCategoryController::class], function () {
    Route::post('/categories/product', 'store');
    Route::get('/categories/product', 'index');
    Route::get('/categories/product/{id}', 'show');
    Route::put('/categories/product/{id}', 'update');
    Route::delete('/categories/product/{id}', 'destroy');
});
Route::group(['prefix' => 'api', 'controller' => StoreCategoryController::class], function () {
    Route::post('/categories/store', 'store');
    Route::get('/categories/store', 'index');
    Route::get('/categories/store/{id}', 'show');
    Route::put('/categories/store/{id}', 'update');
    Route::delete('/categories/store/{id}', 'destroy');
});
