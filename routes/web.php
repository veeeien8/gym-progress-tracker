<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\WorkoutExerciseController;
use App\Http\Controllers\WorkoutSetController;
use App\Http\Controllers\WorkoutTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'workouts.index' : 'login');
});
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('workout-types', WorkoutTypeController::class)->except('show');
    Route::resource('exercises', ExerciseController::class);
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
    Route::get('/workouts/{workout}/attachment', [WorkoutController::class, 'download'])->name('workouts.attachment');
    Route::resource('workouts', WorkoutController::class);
    Route::post('/workouts/{workout}/exercises', [WorkoutExerciseController::class, 'store'])->name('workouts.exercises.store');
    Route::delete('/workouts/{workout}/exercises/{exercise}', [WorkoutExerciseController::class, 'destroy'])->name('workouts.exercises.destroy');
    Route::post('/workouts/{workout}/exercises/{exercise}/sets', [WorkoutSetController::class, 'store'])->name('sets.store');
    Route::get('/workouts/{workout}/sets/{set}/edit', [WorkoutSetController::class, 'edit'])->name('sets.edit');
    Route::put('/workouts/{workout}/sets/{set}', [WorkoutSetController::class, 'update'])->name('sets.update');
    Route::delete('/workouts/{workout}/sets/{set}', [WorkoutSetController::class, 'destroy'])->name('sets.destroy');
});
