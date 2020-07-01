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

use App\Http\Controllers\BankController;
use Laravel\Lumen\Routing\Router;
/** @var Router $router */

$router->group(['prefix' => '/bank', 'as' => 'bank'], function () use ($router) {
    $router->group(['prefix' => '/{accountNumber}'], function () use ($router) {
        $router->get('/balance', [
            'as' => 'balance',
            'uses' => 'BankController@getBalance'
        ]);
    });

    $router->post("/transaction", [
        'as' => 'transaction',
        'uses' => 'BankController@transaction'
        ]
    );

    $router->post("/create_account", [
        'as' => 'createAccount',
        'uses' => 'BankController@createAccount'
        ]
    );
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});
