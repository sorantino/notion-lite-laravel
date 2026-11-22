<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'index')->name('pages.index');
    Route::get('/search', 'search')->name('pages.search');
    Route::get('/trash', 'trash')->name('pages.trash');

    Route::get('/pages/create', 'create')->name('pages.create');
    Route::post('/pages', 'store')->name('pages.store');

    Route::whereNumber('id')->group(function () {
        Route::get('/pages/{id}', 'show')->name('pages.show');
        Route::get('/pages/{id}/edit', 'edit')->name('pages.edit');
        Route::put('/pages/{id}', 'update')->name('pages.update');
        Route::post('/pages/{id}/archive', 'archive')->name('pages.archive');
        Route::post('/pages/{id}/restore', 'restore')->name('pages.restore');
        Route::delete('/pages/{id}', 'destroy')->name('pages.destroy');
    });
});
