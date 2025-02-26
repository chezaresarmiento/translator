<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\MailController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/process-audio', [AudioController::class, 'processAudio']);

Route::get('/pusher-key', function () {
    return response()->json([
        'key' => env('PUSHER_APP_KEY'),
        'cluster' => env('PUSHER_APP_CLUSTER'),
    ]);
});

Route::post("/sendMail", [MailController::class, 'sendMail']);