<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;


Route::get('/', function () {
    return 'start and this is super';
});


Route::post('/task', [TaskController::class, 'store']);
Route::post('/tag', [TagController::class, 'store']);

Route::get('/post', [PostController::class, 'index'])->name('main.index1');
Route::get('/post/create', [PostController::class, 'create']);
Route::get('/post/update', [PostController::class, 'update']);
Route::get('/post/delete', [PostController::class, 'delete']);
Route::get('/test', [PostController::class, 'test']);