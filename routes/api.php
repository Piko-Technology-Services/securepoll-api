<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

});

Route::get('/', function () {
    $health = [
        'Status' => '3FA Secure Poll is running',
        'database' => 'error',
        'email' => 'error',
    ];

    try {
        \DB::connection()->getPdo();
        $health['database'] = 'ok';
    } catch (\Exception $e) {
        $health['status'] = 'error';
    }

    try {
        \Mail::raw('Health check', function ($message) {
            $message->to('katongbupe444@gmail.com');
        });
        $health['email'] = 'ok';
    } catch (\Exception $e) {
        $health['status'] = 'error';
    }

    return response()->json($health);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
