<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
});

Route::get('/sapa/{name?}', function ($nama = 'Semua') {
    //parameter   =
    return "Hallo, Selamat datang $nama di toko online!";
});
