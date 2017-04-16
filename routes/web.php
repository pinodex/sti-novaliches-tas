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

Route::group([
    'namespace'     => 'Dashboard',
    'prefix'        => 'dashboard',
    'as'            => 'dashboard.',
    'middleware'    => ['auth', 'require_password_change', 'provider:user']
], function () {

    Route::get('/', 'MainController@index')->name('index');

    Route::group(['prefix' => 'requests', 'as' => 'requests.'], function () {

        Route::get('/', 'RequestController@index')->name('index');
        Route::get('/me', 'RequestController@me')->name('me');
        Route::match(['get', 'post'], '/create', 'RequestController@create')->name('create');
        Route::get('/{model}', 'RequestController@view')->name('view');

    });

    Route::group(['prefix' => 'bulletins', 'as' => 'bulletins.'], function () {

        Route::get('/', 'BulletinController@index')->name('index');
        Route::match(['get', 'post'], '/create', 'BulletinController@edit')->name('create');
        Route::match(['get', 'post'], '/{model}/edit', 'BulletinController@edit')->name('edit');
        Route::post('/{model}/delete', 'BulletinController@delete')->name('delete');

    });

    // UserController
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {

        Route::get('/', 'UserController@index')->name('index');
        Route::get('/deleted', 'UserController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'UserController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'UserController@edit')->name('edit');
        Route::post('/{model}/delete', 'UserController@delete')->name('delete');
        Route::post('/restore', 'UserController@restore')->name('restore');
        Route::post('/purge', 'UserController@purge')->name('purge');
        Route::get('/{model}', 'UserController@view')->name('view');

    });

    // GroupController
    Route::group(['prefix' => 'groups', 'as' => 'groups.'], function () {

        Route::get('/', 'GroupController@index')->name('index');
        Route::get('/deleted', 'GroupController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'GroupController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'GroupController@edit')->name('edit');
        Route::post('/{model}/delete', 'GroupController@delete')->name('delete');
        Route::match(['get', 'post'], '/{model}/delete/confirm', 'GroupController@deleteConfirm')->name('delete.confirm');
        Route::post('/restore', 'GroupController@restore')->name('restore');
        Route::post('/purge', 'GroupController@purge')->name('purge');

    });

    // DepartmentController
    Route::group(['prefix' => 'departments', 'as' => 'departments.'], function () {

        Route::get('/', 'DepartmentController@index')->name('index');
        Route::get('/deleted', 'DepartmentController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'DepartmentController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'DepartmentController@edit')->name('edit');
        Route::post('/{model}/delete', 'DepartmentController@delete')->name('delete');
        Route::match(['get', 'post'], '/{model}/delete/confirm', 'DepartmentController@deleteConfirm')->name('delete.confirm');
        Route::post('/restore', 'DepartmentController@restore')->name('restore');
        Route::post('/purge', 'DepartmentController@purge')->name('purge');

    });

    // EmployeesController
    Route::group(['prefix' => 'employees', 'as' => 'employees.'], function () {

        Route::get('/', 'EmployeeController@index')->name('index');
        Route::get('/deleted', 'EmployeeController@deleted')->name('deleted');
        Route::match(['get', 'post'], '/add', 'EmployeeController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'EmployeeController@edit')->name('edit');
        Route::post('/{model}/delete', 'EmployeeController@delete')->name('delete');
        Route::post('/restore', 'EmployeeController@restore')->name('restore');
        Route::post('/purge', 'EmployeeController@purge')->name('purge');
        Route::get('/{model}', 'EmployeeController@view')->name('view');
        Route::get('/{model}/logs', 'EmployeeController@logs')->name('logs');

    });

});

Route::group([
    'namespace'     => 'Employee',
    'prefix'        => 'employee',
    'as'            => 'employee.',
    'middleware'    => ['auth', 'require_password_change', 'provider:employee']
], function () {

    Route::get('/', 'MainController@index')->name('index');

});

Route::group(['namespace' => 'Account', 'prefix' => 'account', 'as' => 'account.', 'middleware' => 'auth'], function () {

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {

        Route::match(['get', 'post'], '/', 'SettingsController@index')->name('index');

    });

});
