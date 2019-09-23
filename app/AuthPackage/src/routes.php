<?php

Route::namespace('VATSIMUK\Auth\Remote\Controllers')->prefix('auth')->name('auth.')->group(function () {
    Route::get('login', 'AuthenticationController@login')->name('login');
    Route::get('login/verify', 'AuthenticationController@verifyLogin')->name('login.verify');
});
