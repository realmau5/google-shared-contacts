<?php


// basic home:
Route::get('/', ['uses' => 'HomeController@index', 'as' => 'index', 'middleware' => ['secure', 'auth.google.reversed']]);
Route::get('/home', ['uses' => 'HomeController@home', 'as' => 'home', 'middleware' => ['secure', 'auth.google']]);
Route::get('/privacy', ['uses' => 'HomeController@privacy', 'as' => 'privacy']);

// everything auth related.
Route::post('/login', ['uses' => 'AuthController@loginOAuth2', 'as' => 'oauth.form-submit', 'middleware' => 'secure']);
Route::get('/oauth2callback', ['uses' => 'AuthController@oauth2callback', 'as' => 'oauth2callback']);
Route::get('/logout', ['uses' => 'AuthController@logout', 'as' => 'oauth.logout', 'middleware' => ['secure', 'auth.google']]);
Route::get('/auth', ['uses' => 'AuthController@redirect', 'as' => 'oauth.redirect']);

// mass delete:
Route::post('/massdelete', ['uses' => 'ContactsController@massDelete', 'middleware' => ['secure', 'auth.google']]);
Route::post('/reallymassdelete', ['uses' => 'ContactsController@reallyMassDelete', 'middleware' => ['secure', 'auth.google']]);

// mass create
Route::get('/mass-create', ['uses' => 'MassCreateController@index', 'as' => 'mass-create.index', 'middleware' => ['secure', 'auth.google']]);
Route::post('/mass-create/upload', ['uses' => 'MassCreateController@upload', 'as' => 'mass-create.upload', 'middleware' => ['secure', 'auth.google']]);
Route::get('/mass-create/example', ['uses' => 'MassCreateController@getExampleFile', 'as' => 'mass-create.example', 'middleware' => ['secure', 'auth.google']]);


// add/edit, delete, etc:
Route::get('/add', ['uses' => 'ContactsController@add', 'as' => 'contacts.add', 'middleware' => ['secure', 'auth.google']]);
Route::get('/edit/{code}', ['uses' => 'ContactsController@edit', 'as' => 'contacts.edit', 'middleware' => ['secure', 'auth.google']]);
Route::get('/delete/{code}', ['uses' => 'ContactsController@delete', 'as' => 'contacts.delete', 'middleware' => ['secure', 'auth.google']]);
Route::get('/mng-photo/{code}', ['uses' => 'ContactsController@editPhoto', 'as' => 'contacts.manage-photo', 'middleware' => ['secure', 'auth.google']]);
Route::post('/add', ['uses' => 'ContactsController@postAdd', 'middleware' => ['secure', 'auth.google']]);
Route::post('/edit/{code}', ['uses' => 'ContactsController@postEdit', 'middleware' => ['secure', 'auth.google']]);
Route::post('/delete/{code}', ['uses' => 'ContactsController@postDelete', 'middleware' => ['secure', 'auth.google']]);

// JSON and AJAX code:
Route::get('/rpc/addrow/{tpl}/{index}', ['uses' => 'RpcController@addRow', 'as' => 'edit.addRow', 'middleware' => ['secure', 'auth.google']]);

// photos and images
Route::get('/photo/{code}', ['uses' => 'PhotoController@photo', 'as' => 'contacts.photo', 'middleware' => ['secure', 'auth.google']]);