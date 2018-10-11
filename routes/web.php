<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/books', 'BooksController@index');
$router->get('/books/{id:[\d]+}', [
    'as' => 'books.show',
    'uses' => 'BooksController@show'
]);
$router->post('/books', 'BooksController@store');
$router->put('/books/{id:[\d]+}', 'BooksController@update');
$router->delete('/books/{id:[\d]+}', 'BooksController@destroy');

$router->group([
    'prefix' => '/authors',
    'namespace' => '\App\Http\Controllers'
], function () use ($router) {
    $router->get('/', 'AuthorController@index');
    $router->post('/', 'AuthorController@store');
    $router->get('/{id:[\d]+}', [
        'as' => 'authors.show',
        'uses' => 'AuthorController@show'
    ]);
    $router->put('/{id:[\d]+}', 'AuthorController@update');
    $router->delete('/{id:[\d]+}', 'AuthorController@destroy');
});

$router->group([
    'prefix' => '/bundles',
    'namespace' => '\App\Http\Controllers'
], function () use ($router) {
    $router->get('/{id:[\d]+}', [
        'as' => 'bundles.show',
        'uses' => 'BundlesController@show'
    ]);
    $router->put('/{bundleId:[\d]+}/books/{bookId:[\d]+}', 'BundlesController@addBook');
    $router->delete('/{bundleId:[\d]+}/books/{bookId:[\d]+}', 'BundlesController@removeBook');
});
