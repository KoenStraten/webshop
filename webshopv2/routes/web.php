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

Route::get('/', 'HomeController@index');

Route::get('/product/{product}', 'HomeController@show');

Route::get('/category/{category}', 'CategoryController@index');

Route::post('/shoppingcart/store/', 'ShoppingCartController@store');
Route::post('/shoppingcart/remove', 'ShoppingCartController@remove');
Route::get('/shoppingcart', 'ShoppingCartController@show');

Auth::routes();

Route::post('/postReview', 'ReviewController@store');

Route::get('/admin/dashboard', 'AdminController@index');

Route::get('/search', 'SearchController@index');

Route::get('/about', function () {
    return view('pages/about');
});

Route::get('/category', function () {
    return view('pages/category');
});

Route::get('/database_eer', function () {
    return view('designs/eer');
});