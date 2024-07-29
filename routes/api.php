<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::group(['middleware' => 'UserIsAuthenticatedMiddleware'],  static function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/send-verification-email',[AuthController::class, 'SendVerifyEmail']);
    Route::get('/verify-email',[AuthController::class, 'VerifyEmail']);
    Route::get('/refresh-token',[AuthController::class, 'refreshToken']);
    Route::post('/verify-email-code',[AuthController::class, 'verifyEmailCode']);
    Route::post('/Verify2FA',[AuthController::class, 'verify2FA']);

});

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
