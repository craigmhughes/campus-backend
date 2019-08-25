<?php

use Illuminate\Http\Request;
use App\Events\RequestUpdate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    'middleware' => ['api'],
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('register', 'AuthController@register');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

    Route::post('update', 'AuthController@update_user');
});

Route::group([

    'middleware' => ['auth:api']

], function ($router){
  
    Route::resource('/posts', 'PostsController', ['parameters' => ['post' => 'id']]);
    Route::get('/requests', 'RequestsController@index');
    Route::post('/requests', 'RequestsController@store');
    Route::delete('/requests', 'RequestsController@destroy');
    Route::get('/requests/count', 'RequestsController@count');
    Route::resource('conversations', 'ConversationsController');

    Route::resource('/connections', 'ConnectionsController');
    Route::delete('/connections', 'ConnectionsController@destroy');
    Route::post('/search/uni', 'SearchController@uni');
    Route::get('/search/users', 'SearchController@users');

    // Route::post('/requests', function(){

    //     $message = request();

    //     event(new RequestUpdate($message));

    //     return response()->json(["success" => "pushed to pusher"], 200);

    // });
    
});

