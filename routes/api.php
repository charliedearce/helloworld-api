<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('/login', 'AuthController@login');
Route::post('/register-client', 'AuthController@registerClient');
Route::post('/social-client', 'AuthController@registerClientSocial');
Route::post('/register-therapist', 'AuthController@registerTherapist');

Route::group(['middleware' => ['auth:api']], function(){
    Route::post('/logout', 'AuthController@logout');

    Route::get('/profile', 'SettingsController@profileDetails');
    Route::post('/profile', 'SettingsController@updateProfile');
    Route::post('/changepassword', 'SettingsController@changePassword');

    Route::post('/image', 'SettingsController@uploadImage');
});



