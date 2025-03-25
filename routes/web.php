<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TimeIntervalController;


Route::get('/', function () {
    return 'start and this is super';
});


Route::post('/task', [TaskController::class, 'store'])->middleware('validate.token');
Route::post('/tag', [TagController::class, 'store'])->middleware('validate.token');

Route::post('/task/{task_id}/start', [TimeIntervalController::class, 'start'])->middleware('validate.token');
Route::post('/task/{task_id}/stop', [TimeIntervalController::class, 'stop'])->middleware('validate.token');