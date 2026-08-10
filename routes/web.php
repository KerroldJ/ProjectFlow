<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'projects.index' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Inertia page shell — project data is fetched client-side from the
    // JSON REST API defined in routes/api.php.
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
});

require __DIR__.'/settings.php';
