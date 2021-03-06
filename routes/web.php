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

Route::get("/", function () {
    return view("welcome");
});

Auth::routes(["verify" => true]);

Route::group(
    ["as" => "login.", "prefix" => "login", "namespace" => "Auth"],
    function () {
        Route::get("/{provider}", "LoginController@redirectToProvider")->name(
            "provider"
        );
        Route::get(
            "/{provider}/callback",
            "LoginController@handleProviderCallback"
        )->name("callback");
    }
);

Route::get("/home", "HomeController@index")
    ->name("home")
    ->middleware("verified");

Route::get("download", "StorageController@download")->name("download");
