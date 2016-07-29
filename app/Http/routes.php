<?php

// basic home:
Route::get('/', ['uses' => 'HomeController@index', 'as' => 'index', 'middleware' => ['auth.google.reversed']]);
Route::get('/home', ['uses' => 'HomeController@home', 'as' => 'home', 'middleware' => ['auth.google']]);
Route::get('/privacy', ['uses' => 'HomeController@privacy', 'as' => 'privacy']);

// everything auth related.
Route::post('/login', ['uses' => 'Auth\AuthController@loginOAuth2', 'as' => 'oauth.form-submit']);
Route::get('/oauth2callback', ['uses' => 'Auth\AuthController@oauth2callback', 'as' => 'oauth2callback']);
Route::get('/logout', ['uses' => 'Auth\AuthController@logout', 'as' => 'oauth.logout', 'middleware' => ['auth.google']]);
Route::get('/auth', ['uses' => 'Auth\AuthController@redirect', 'as' => 'oauth.redirect']);

// mass delete:
Route::post('/massdelete', ['uses' => 'ContactsController@massDelete', 'as' => 'massdelete', 'middleware' => ['auth.google']]);
Route::post('/reallymassdelete', ['uses' => 'ContactsController@reallyMassDelete', 'as' => 'reallymassdelete', 'middleware' => ['auth.google']]);

// mass create
Route::get('/mass-create', ['uses' => 'MassCreateController@index', 'as' => 'mass-create.index', 'middleware' => ['auth.google']]);
Route::post('/mass-create/upload', ['uses' => 'MassCreateController@upload', 'as' => 'mass-create.upload', 'middleware' => ['auth.google']]);
Route::get('/mass-create/example', ['uses' => 'MassCreateController@getExampleFile', 'as' => 'mass-create.example', 'middleware' => ['auth.google']]);


// add/edit, delete, etc:
Route::get('/add', ['uses' => 'ContactsController@add', 'as' => 'contacts.add', 'middleware' => ['auth.google']]);
Route::get('/edit/{code}', ['uses' => 'ContactsController@edit', 'as' => 'contacts.edit', 'middleware' => ['auth.google']]);
Route::get('/delete/{code}', ['uses' => 'ContactsController@delete', 'as' => 'contacts.delete', 'middleware' => ['auth.google']]);
Route::get('/mng-photo/{code}', ['uses' => 'ContactsController@editPhoto', 'as' => 'contacts.manage-photo', 'middleware' => ['auth.google']]);
Route::post('/add', ['uses' => 'ContactsController@postAdd', 'as' => 'contacts.store', 'middleware' => ['auth.google']]);
Route::post('/edit/{code}', ['uses' => 'ContactsController@postEdit', 'as' => 'contacts.update', 'middleware' => ['auth.google']]);
Route::post('/delete/{code}', ['uses' => 'ContactsController@postDelete', 'as' => 'contacts.delete', 'middleware' => ['auth.google']]);

// JSON and AJAX code:
Route::get('/rpc/addrow/{tpl}/{index}', ['uses' => 'RpcController@addRow', 'as' => 'edit.addRow', 'middleware' => ['auth.google']]);

// photos and images
Route::get('/photo/{code}', ['uses' => 'PhotoController@photo', 'as' => 'contacts.photo', 'middleware' => ['auth.google']]);

