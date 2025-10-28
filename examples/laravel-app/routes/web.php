<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Projects
Route::resource('projects', ProjectController::class);
Route::post('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');

// Tasks
Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
Route::post('tasks/{task}/toggle', [TaskController::class, 'toggleComplete'])->name('tasks.toggle');
Route::post('tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');

// Tags
Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);
