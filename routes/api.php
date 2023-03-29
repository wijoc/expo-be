<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\StoreCategoryController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliveryController;
use GuzzleHttp\Middleware;

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
    Route::get('/stores/{store}/products', [StoreController::class, 'productInStore']);

    // Product Routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/similar/{product}', [ProductController::class, 'similar']);

    // Categories Routes
    Route::get('/categories/product', [ProductCategoryController::class, 'index']);
    Route::get('/categories/store', [StoreCategoryController::class, 'index']);

    // Region Routes
    Route::get('/provinces', [ProvinceController::class, 'index']);
    Route::get('/provinces/{id}', [ProvinceController::class, 'show']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/cities/{id}', [CityController::class, 'show']);
    Route::get('/districts', [DistrictController::class, 'index']);
    Route::get('/districts/{id}', [DistrictController::class, 'show']);

    // Delivery Service Routes
    Route::get('/delivery-services', [DeliveryController::class, 'index']);
    Route::get('/delivery-services/{id}', [DeliveryController::class, 'show']);
  });

/** Protected Route */
  // User Protected Routes
    Route::group(['prefix' => 'beta', 'controller' => UserController::class], function () {
      Route::get('/users', 'index')->middleware('jauth:su|admin');
      Route::post('/users', 'store')->middleware('jauth:su|admin');
      Route::get('/my-profile', 'userProfile')->middleware('jauth');
      Route::post('/refresh-access-token', 'refreshToken')->middleware('jauth');
      Route::post('/users/address', 'storeAddress')->middleware('jauth');
    });

  // Store Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth', 'controller' => StoreController::class], function () {
      Route::post('/stores', 'store');
      Route::post('/stores/{id}', 'updateImage');
      Route::put('/stores/{id}', 'update');
      Route::delete('/stores/{id}', 'destroy');
    });

  // Product Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth', 'controller' => ProductController::class], function () {
      Route::post('/products', 'store');
      Route::post('/products/{id}', 'updateImage');
      Route::put('/products/{id}', 'update');
      Route::delete('/products/{id}', 'destroy');
      Route::post('/products/add-image/{id}', 'addImage');
      Route::delete('/products/delete-image/{id}', 'deleteImage');
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

  // Cart Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth', 'controller' => CartController::class], function () {
      Route::get('/cart', 'index');
      Route::post('/cart', 'store');
      Route::put('/cart/{id}', 'update');
      Route::delete('/cart/{id}', 'destroy');
    });

  // Order Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth', 'controller' => OrderController::class], function () {
      Route::get('/orders', 'index');
      Route::get('/orders/{code}', 'show');
      Route::post('/orders', 'store');
    });

  // Delivery Service Protected Routes
    Route::group(['prefix' => 'beta', 'middleware' => 'jauth:su', 'controller' => CartController::class], function () {
      Route::post('/delivery-services', 'store');
      Route::put('/delivery-services/{id}', 'update');
      Route::delete('/delivery-services/{id}', 'destroy');
    });
