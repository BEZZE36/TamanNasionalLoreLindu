<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Image Serving Routes
|--------------------------------------------------------------------------
| Routes for serving images from database storage
*/

Route::get('/flora/{id}', [App\Http\Controllers\ImageController::class, 'flora'])->name('flora');
Route::get('/flora-gallery/{id}', [App\Http\Controllers\ImageController::class, 'floraGallery'])->name('flora_gallery');
Route::get('/fauna/{id}', [App\Http\Controllers\ImageController::class, 'fauna'])->name('fauna');
Route::get('/fauna-gallery/{id}', [App\Http\Controllers\ImageController::class, 'faunaGallery'])->name('fauna_gallery');
Route::get('/gallery/{id}', [App\Http\Controllers\ImageController::class, 'gallery'])->name('gallery');
Route::get('/gallery-media/{id}', [App\Http\Controllers\ImageController::class, 'galleryMedia'])->name('gallery_media');
Route::get('/banner/{id}/{type?}', [App\Http\Controllers\ImageController::class, 'banner'])->name('banner');
Route::get('/destination/{id}', [App\Http\Controllers\ImageController::class, 'destinationImage'])->name('destination');
Route::get('/article/{id}', [App\Http\Controllers\ImageController::class, 'article'])->name('article');
Route::get('/upload/{hash}', [App\Http\Controllers\ImageController::class, 'upload'])->name('upload');
