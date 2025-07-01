<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ManageVideos;

Route::middleware('auth')->group(function () {
    Route::view('/generate-ticket', 'welcome')->name('home');

    Route::view('/', 'dashboard')->middleware('verified')->name('dashboard');

    Route::view('profile', 'profile')->name('profile');
    
    Route::get('/create-area', function () {
        return view('create-area');
    })->name('create-area');
    Route::get('/manage', function () {
        return view('manage');
    })->name('manage');
    Route::get('/display', function () {
        return view('display');
    })->name('display');
    Route::get('/videos', function () {
        return view('videos');
    })->name('videos');
    Route::view('/puestos', 'puestos')->name('puestos');
    Route::get('/asignar-puesto', function () {
        return view('asignar-puesto');
    })->middleware('auth')->name('asignar-puesto');
   
    

    
});

require __DIR__.'/auth.php';
