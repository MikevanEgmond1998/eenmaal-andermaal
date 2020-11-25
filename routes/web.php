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

Route::get('/registreren', 'Auth\RegisterController@index')->name('register');
Route::post('/registreren', 'Auth\RegisterController@create');
Route::post('/registreren/verify', 'Auth\RegisterController@send_verify');


Route::get('/login', 'Auth\LoginController@index')->name('login');
Route::post('/login', 'Auth\LoginController@login');

Route::get('/wachtwoordvergeten', 'Auth\ForgotPasswordController@index')->name('wachtwoordvergeten');

Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('foo', function () {
    $cc = \App\DB::selectOne("SELECT * FROM security_questions WHERE id=:id",[
        "id" => 1
    ]);
    dd($cc);
});
