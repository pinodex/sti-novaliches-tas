<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

Route::get('/', ['as' => 'index', 'uses' => 'MainController@index']);

Route::match(['get', 'post'], '/login', ['as' => 'auth.login', 'uses' => 'AuthController@login', 'middleware' => ['guest']]);

Route::get('/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);

Route::group(['namespace' => 'Dashboard', 'prefix' => 'd', 'as' => 'dashboard.'], function () {

    Route::get('/', 'MainController@index')->name('index');

    // UsersController
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {

        Route::get('/', 'UsersController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'UsersController@edit')->name('add');

        Route::match(['get', 'post'], '/add/validate', 'UsersController@validate')->name('add.validate');

        Route::match(['get', 'post'], '/{model}/edit', 'UsersController@edit')->name('edit');

    });
});

