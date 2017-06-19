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

Route::get('/notifications', ['as' => 'notifications', 'uses' => 'MainController@notifications']);

Route::get('/notifications/{notification}', ['as' => 'notifications.view', 'uses' => 'MainController@viewNotification']);

Route::group([
    'prefix'    => 'auth',
    'as'        => 'auth.'
], function () {

    Route::match(['get', 'post'], '/login', 'AuthController@login')->name('login')->middleware('guest');
    Route::get('/logout', 'AuthController@logout')->name('logout');

    Route::match(['get', 'post'], '/reset/{email}/{token}', 'AuthController@reset')->name('reset')->middleware('guest');
    
});

Route::group([
    'namespace'     => 'Admin',
    'prefix'        => 'admin',
    'as'            => 'admin.',
    'middleware'    => ['auth', 'require_password_change']
], function () {

    Route::group(['prefix' => 'requests', 'as' => 'requests.'], function () {

        Route::get('/', 'RequestController@index')->name('index');
        Route::get('/{model}', 'RequestController@view')->name('view');
        Route::get('/{model}/printable', 'RequestController@printable')->name('printable');
        Route::match(['get', 'post'], '/{model}/edit', 'RequestController@edit')->name('edit');

    });

    Route::group(['prefix' => 'bulletins', 'as' => 'bulletins.'], function () {

        Route::get('/', 'BulletinController@index')->name('index');
        Route::match(['get', 'post'], '/create', 'BulletinController@edit')->name('create');
        Route::match(['get', 'post'], '/{model}/edit', 'BulletinController@edit')->name('edit');
        Route::post('/{model}/delete', 'BulletinController@delete')->name('delete');

    });

    // UserController
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {

        Route::group(['prefix' => 'import', 'as' => 'import.'], function () {

            Route::get('/', 'UserImportController@index')->name('index');
            Route::get('/download', 'UserImportController@download')->name('download');
            Route::match(['get', 'post'], '/upload', 'UserImportController@upload')->name('upload');
            Route::match(['get', 'post'], '/session/{id}/confirm', 'UserImportController@confirm')->name('confirm');
            Route::get('/session/{id}/finish', 'UserImportController@finish')->name('finish');

        });

        Route::get('/', 'UserController@index')->name('index');
        Route::get('/deleted', 'UserController@deleted')->name('deleted');
        Route::post('/purge', 'UserController@purge')->name('purge');
        Route::post('/restore', 'UserController@restore')->name('restore');
        Route::match(['get', 'post'], '/add', 'UserController@edit')->name('add');
        Route::get('/{model}', 'UserController@view')->name('view');
        Route::get('/{model}/logs', 'UserController@logs')->name('logs');
        Route::post('/{model}/delete', 'UserController@delete')->name('delete');
        Route::post('/{model}/reset-password', 'UserController@resetPassword')->name('reset_password');
        Route::match(['get', 'post'], '/{model}/edit', 'UserController@edit')->name('edit');

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

    // ProfileController
    Route::group(['prefix' => 'profiles', 'as' => 'profiles.'], function () {

        Route::get('/', 'ProfileController@index')->name('index');
        Route::match(['get', 'post'], '/add', 'ProfileController@edit')->name('add');
        Route::match(['get', 'post'], '/{model}/edit', 'ProfileController@edit')->name('edit');
        Route::post('/{model}/delete', 'ProfileController@delete')->name('delete');
        Route::get('/{model}', 'ProfileController@view')->name('view');

    });

    // ProfileController
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {

        Route::get('/', 'ConfigurationController@index')->name('index');

    });

});

Route::group([
    'namespace'     => 'Employee',
    'prefix'        => 'employee',
    'as'            => 'employee.',
    'middleware'    => ['auth', 'require_password_change']
], function () {

    Route::group(['prefix' => 'requests', 'as' => 'requests.'], function () {

        Route::group(['namespace' => 'Requests', 'prefix' => 'inbox', 'as' => 'inbox.'], function () {

            Route::get('/', 'InboxController@index')->name('index');
            Route::get('/{model}', 'InboxController@view')->name('view');
            Route::get('/{model}/printable', 'InboxController@printable')->name('printable');
            Route::post('/{model}/approve', 'InboxController@approve')->name('approve');
            Route::post('/{model}/disapprove', 'InboxController@disapprove')->name('disapprove');

        });

        Route::get('/', 'RequestController@index')->name('index');
        Route::post('/create', 'RequestController@create')->name('create');
        Route::match(['get', 'post'], '/create/{type}', 'RequestController@createType')->name('create.type');
        Route::get('/{model}', 'RequestController@view')->name('view');
        Route::get('/{model}/printable', 'RequestController@printable')->name('printable');

    });

});

Route::group(['namespace' => 'Account', 'prefix' => 'account', 'as' => 'account.', 'middleware' => 'auth'], function () {

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {

        Route::match(['get', 'post'], '/', 'SettingsController')->name('index');

    });

    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::get('/', 'NotificationController@index')->name('index');
        Route::get('/{model}', 'NotificationController@view')->name('view');
        Route::post('/read', 'NotificationController@read')->name('read');
    });

});
