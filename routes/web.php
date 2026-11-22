<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::controller(NoteController::class)->group(function () {
    Route::get('/', 'index')->name('notes.index');
    Route::get('/search', 'search')->name('notes.search');
    Route::get('/trash', 'trash')->name('notes.trash');

    Route::get('/notes/create', 'create')->name('notes.create');
    Route::post('/notes', 'store')->name('notes.store');

    Route::whereNumber('id')->group(function () {
        Route::get('/notes/{id}', 'show')->name('notes.show');
        Route::get('/notes/{id}/edit', 'edit')->name('notes.edit');
        Route::put('/notes/{id}', 'update')->name('notes.update');
        Route::post('/notes/{id}/summarize', 'summarize')->name('notes.summarize');
        Route::post('/notes/{id}/archive', 'archive')->name('notes.archive');
        Route::post('/notes/{id}/restore', 'restore')->name('notes.restore');
        Route::delete('/notes/{id}', 'destroy')->name('notes.destroy');
    });
});
