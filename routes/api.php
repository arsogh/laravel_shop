<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::resource('categories', CategoryController::class);
Route::Resource('products', ProductController::class)->only(['index', 'show']);

Route::group(['middleware' => 'api'], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('me', [AuthController::class, 'me']);

    Route::group(['prefix' => 'auth'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('register', [AuthController::class, 'register']);
    });

    Route::Resource('shops', ShopController::class)->only(['index', 'show']);
//    Route::Resource('shops.products', ProductController::class)->only(['index', 'show']);
    Route::Resource('rates', RateController::class)->only(['index']);

    Route::group(['middleware' => 'userType:seller'], function() {
        Route::Resource('shops', ShopController::class)->except(['index', 'show']);
        Route::Resource('shops.products', ProductController::class)->except(['index', 'show']);
        Route::resource('images', ProductImageController::class);
        Route::post('rates/{id}/report', [RateController::class, 'report']);
    });

    Route::group(['middleware' => 'userType:buyer'], function () {
        Route::get('carts/checkout', [CartController::class, 'checkout']);
        Route::resource('carts', CartController::class);
        Route::resource('rates', RateController::class);
    });

    Route::group(['middleware' => 'userType:admin', 'prefix' => 'admin'], function () {
//        Route::resource('categories', CategoryController::class);
        Route::Resource('users', UserController::class);
        Route::Resource('shops', ShopController::class)->except(['index', 'show']);
        Route::Resource('shops.products', ProductController::class)->except(['index', 'show']);
        Route::resource('images', ProductImageController::class);
        Route::resource('carts', CartController::class);
        Route::post('rates/{id}/reported_rates', [RateController::class, 'reported_rates']);
        Route::resource('rates', RateController::class);
    });
});