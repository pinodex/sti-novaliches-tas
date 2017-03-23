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

    // UsersController
    Route::group(['prefix' => 'users', 'as' => 'users.', 'middleware' => 'can:manage_users'], function () {
        Route::get('/', 'UsersController@index')->name('index');
        Route::get('/deleted', 'UsersController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'UsersController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'UsersController@edit')->name('edit');
        Route::match(['get', 'post'], '/{model}/delete', 'UsersController@delete')->name('delete');
        Route::post('/restore', 'UsersController@restore')->name('restore');
        Route::post('/purge', 'UsersController@purge')->name('purge');
    });

    // GroupsController
    Route::group(['prefix' => 'groups', 'as' => 'groups.', 'middleware' => 'can:manage_groups'], function () {
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
    Route::group(['prefix' => 'departments', 'as' => 'departments.', 'middleware' => 'can:manage_departments'], function () {
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

