<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;


Route::get('/', function () {
    return 'start and this is super';
});


Route::post('/task', [TaskController::class, 'store'])->middleware('validate.token');
Route::post('/tag', [TagController::class, 'store']);