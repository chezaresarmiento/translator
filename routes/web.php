<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/audio/{filename}', function ($filename) {
    $path = 'audio/' . $filename;

    if (! Storage::disk('local')->exists($path)) {
        abort(404);
    }

    $fileContents = Storage::disk('local')->get($path);
    $mimeType = 'audio/wav'; // or detect via mime_content_type

    return response($fileContents, 200)->header('Content-Type', $mimeType);
})->name('audio.stream');



require __DIR__.'/auth.php';
