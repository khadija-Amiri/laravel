<?php

use Illuminate\Support\Facades\Route;


Route::resource('gallery', 'GalleryController');


Route::get('/', function () {
    return view('welcome');
});
