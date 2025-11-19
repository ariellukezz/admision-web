<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin/v2/')->middleware('admin','auth')->group(function () {
    Route::get('/observados', fn () => Inertia::render('Admin/v2/Observados/index'))->name('adminv2.observados');
});











