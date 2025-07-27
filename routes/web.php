<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/uploadvetor', function () {
    return view('uploadvetor');
});

Route::get('/qachat', function () {
    return view('qachat');
});