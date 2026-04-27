<?php

use Canva\HBD\Http\Controllers\HbdController;
use Canva\HBD\Http\Middleware\HbdAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware([HbdAdmin::class])
    ->prefix('hbd')
    ->name('hbd.')
    ->group(function () {
        Route::get('/', [HbdController::class, 'index'])->name('index');
        Route::get('/settings', [HbdController::class, 'settings'])->name('settings');
        Route::post('/settings', [HbdController::class, 'saveSettings'])->name('settings.save');
        Route::get('/canvas', [HbdController::class, 'canvas'])->name('canvas');
        Route::post('/canvas', [HbdController::class, 'saveCanvas'])->name('canvas.save');
        Route::get('/canvas/preview', [HbdController::class, 'canvasPreview'])->name('canvas.preview');
        Route::post('/enviar/{userId}', [HbdController::class, 'enviar'])->name('enviar');
        Route::post('/enviar-test/{userId}', [HbdController::class, 'enviarTest'])->name('enviar.test');
        Route::post('/canvas/media', [HbdController::class, 'uploadMedia'])->name('canvas.media');
        Route::get('/historial', [HbdController::class, 'historial'])->name('historial');
    });
