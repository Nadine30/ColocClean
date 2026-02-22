<?php

use App\Http\Controllers\ColocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/colocations', [ColocationController::class, 'index'])->name('colocations.index');
    Route::post('/colocations', [ColocationController::class, 'store'])->name('colocations.store');
    Route::post('/colocations/join', [ColocationController::class, 'join'])->name('colocations.join');
    Route::get('/colocations/{colocation}', [ColocationController::class, 'show'])->name('colocations.show');
    Route::post('/colocations/{colocation}/leave', [ColocationController::class, 'leave'])->name('colocations.leave');

    Route::post('/colocations/{colocation}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/colocations/{colocation}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/colocations/{colocation}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/colocations/{colocation}/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('task-comments.store');

    Route::post('/colocations/{colocation}/task-templates', [TaskTemplateController::class, 'store'])->name('task-templates.store');
    Route::delete('/colocations/{colocation}/task-templates/{taskTemplate}', [TaskTemplateController::class, 'destroy'])->name('task-templates.destroy');
    Route::post('/colocations/{colocation}/task-templates/{taskTemplate}/create-task', [TaskTemplateController::class, 'createTask'])->name('task-templates.create-task');
    Route::post('/colocations/{colocation}/task-templates/generate-week', [TaskTemplateController::class, 'generateWeek'])->name('task-templates.generate-week');

    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
