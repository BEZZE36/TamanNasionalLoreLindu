<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes - AI Features
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/generate', [\App\Http\Controllers\Admin\AiController::class, 'generate'])->name('generate');
        Route::post('/rewrite', [\App\Http\Controllers\Admin\AiController::class, 'rewrite'])->name('rewrite');
        Route::post('/summarize', [\App\Http\Controllers\Admin\AiController::class, 'summarize'])->name('summarize');
        Route::post('/seo-suggest', [\App\Http\Controllers\Admin\AiController::class, 'seoSuggest'])->name('seo-suggest');
        Route::post('/headlines', [\App\Http\Controllers\Admin\AiController::class, 'headlines'])->name('headlines');
        Route::post('/expand', [\App\Http\Controllers\Admin\AiController::class, 'expand'])->name('expand');
        Route::post('/shorten', [\App\Http\Controllers\Admin\AiController::class, 'shorten'])->name('shorten');
        Route::post('/translate', [\App\Http\Controllers\Admin\AiController::class, 'translate'])->name('translate');
        Route::post('/improve-grammar', [\App\Http\Controllers\Admin\AiController::class, 'improveGrammar'])->name('improve-grammar');
        Route::post('/generate-seo-tags', [\App\Http\Controllers\Admin\AiController::class, 'generateSeoTags'])->name('generate-seo-tags');
    });
});
