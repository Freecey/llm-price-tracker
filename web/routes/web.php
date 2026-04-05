<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModelController;

Route::get('/', [ModelController::class, 'index'])->name('models.index');
Route::get('/model/{id}', [ModelController::class, 'show'])->name('models.show');
Route::get('/compare', [ModelController::class, 'compare'])->name('models.compare');
Route::get('/providers-analysis', [ModelController::class, 'providers'])->name('models.providers');
Route::get('/providers', [ModelController::class, 'providersList'])->name('providers.list');
Route::get('/trends', [ModelController::class, 'trends'])->name('models.trends');
Route::get('/export', [ModelController::class, 'export'])->name('models.export');
Route::get('/api/search', [ModelController::class, 'apiSearch'])->name('models.apiSearch');
Route::get('/alerts', [ModelController::class, 'alerts'])->name('models.alerts');
Route::get('/tools', [ModelController::class, 'tools'])->name('models.tools');
Route::get('/api/random-model', [ModelController::class, 'randomModel'])->name('models.randomModel');
Route::get('/dashboard', [ModelController::class, 'dashboard'])->name('models.dashboard');
Route::get('/about', [ModelController::class, 'about'])->name('about');
Route::view('/glossary', 'glossary')->name('glossary');
Route::get('/free', [ModelController::class, 'free'])->name('models.free');
