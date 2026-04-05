<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModelController;

Route::get('/', [ModelController::class, 'index'])->name('models.index');
Route::get('/model/{id}', [ModelController::class, 'show'])->name('models.show');
