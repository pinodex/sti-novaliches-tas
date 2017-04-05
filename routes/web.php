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

Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard', 'as' => 'dashboard.', 'middleware' => 'auth'], function () {

    Route::get('/', 'MainController@index')->name('index');

    Route::group(['prefix' => 'requests', 'as' => 'requests.'], function () {

        Route::get('/', 'RequestsController@index')->name('index');
        Route::get('/me', 'RequestsController@me')->name('me');
        Route::match(['get', 'post'], '/create', 'RequestsController@create')->name('create');
        Route::get('/{model}', 'RequestsController@view')->name('view');

    });

    Route::group(['prefix' => 'bulletins', 'as' => 'bulletins.'], function () {

        Route::get('/', 'BulletinsController@index')->name('index');
        Route::match(['get', 'post'], '/create', 'BulletinsController@edit')->name('create');
        Route::match(['get', 'post'], '/{model}/edit', 'BulletinsController@edit')->name('edit');
        Route::match(['get', 'post'], '/{model}/delete', 'BulletinsController@delete')->name('delete');

    });

    // UsersController
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {

        Route::get('/', 'UsersController@index')->name('index');
        Route::get('/deleted', 'UsersController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'UsersController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'UsersController@edit')->name('edit');
        Route::match(['get', 'post'], '/{model}/picture/edit', 'UsersController@pictureEdit')->name('picture.edit');
        Route::match(['get', 'post'], '/{model}/balance/edit', 'UsersController@balanceEdit')->name('balance.edit');
        Route::match(['get', 'post'], '/{model}/delete', 'UsersController@delete')->name('delete');
        Route::post('/restore', 'UsersController@restore')->name('restore');
        Route::post('/purge', 'UsersController@purge')->name('purge');
        Route::get('/{model}', 'UsersController@view')->name('view');

    });

    // GroupsController
    Route::group(['prefix' => 'groups', 'as' => 'groups.'], function () {

        Route::get('/', 'GroupsController@index')->name('index');
        Route::get('/deleted', 'GroupsController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'GroupsController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'GroupsController@edit')->name('edit');
        Route::match(['get', 'post'], '/{model}/delete', 'GroupsController@delete')->name('delete');
        Route::match(['get', 'post'], '/{model}/delete/confirm', 'GroupsController@deleteConfirm')->name('delete.confirm');
        Route::post('/restore', 'GroupsController@restore')->name('restore');
        Route::post('/purge', 'GroupsController@purge')->name('purge');

    });

    // DepartmentsController
    Route::group(['prefix' => 'departments', 'as' => 'departments.'], function () {

        Route::get('/', 'DepartmentsController@index')->name('index');
        Route::get('/deleted', 'DepartmentsController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'DepartmentsController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'DepartmentsController@edit')->name('edit');
        Route::match(['get', 'post'], '/{model}/delete', 'DepartmentsController@delete')->name('delete');
        Route::match(['get', 'post'], '/{model}/delete/confirm', 'DepartmentsController@deleteConfirm')->name('delete.confirm');
        Route::post('/restore', 'DepartmentsController@restore')->name('restore');
        Route::post('/purge', 'DepartmentsController@purge')->name('purge');

    });

});

Route::group(['namespace' => 'Account', 'prefix' => 'account', 'as' => 'account.', 'middleware' => 'auth'], function () {

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {

        Route::match(['get', 'post'], '/', 'SettingsController@index')->name('index');

    });

});
