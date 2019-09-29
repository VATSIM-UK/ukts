<?php

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

use Illuminate\Support\Facades\Auth;

Route::get('/', function () {

    dd(\App\User::find(1300001));


    $b = \App\Booking::with('user:id,name_first')->get();
    dd($b);
    //$u = \App\AuthPackage\User::find(1300001);

//    dd(\App\AuthPackage\User::whereIn('email', ['1@g.com', '2@g.com']));
    dd($b->user()->get(['name_first', 'email']));
    return view('welcome');
});

Route::middleware(['auth'])->get('/secretroute', function () {
    dd(Auth::user()->fresh(['email']));
});
