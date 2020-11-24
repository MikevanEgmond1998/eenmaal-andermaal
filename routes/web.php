<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('home');

//Route::get('auction', function () {
//    return view('auctions.view');
//});

Route::resource('auctions', AuctionController::class);

Route::get('/registeren', 'Auth\RegisterController@index')->name('register');
Route::post('/register', 'Auth\RegisterController@create');


Route::get('/login', 'Auth\LoginController@index')->name('login');
Route::post('/login', 'Auth\LoginController@login');

Route::get('/wachtwoordvergeten', 'Auth\ForgotPasswordController@index')->name('wachtwoordvergeten');

Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('foo', function () {

});
