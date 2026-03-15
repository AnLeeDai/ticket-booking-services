<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.dashboard')->name('home');
Route::view('/dashboard', 'pages.dashboard')->name('dashboard');
Route::view('/login', 'pages.auth.login')->name('login');
Route::view('/profile', 'pages.profile.index')->name('profile.index');
Route::view('/movies', 'pages.movies.index')->name('movies.index');
Route::view('/cinemas', 'pages.cinemas.index')->name('cinemas.index');
Route::view('/categories', 'pages.categories.index')->name('categories.index');
Route::view('/showtimes', 'pages.showtimes.index')->name('showtimes.index');
Route::view('/combos', 'pages.combos.index')->name('combos.index');
Route::view('/booking', 'pages.booking.index')->name('booking.index');
