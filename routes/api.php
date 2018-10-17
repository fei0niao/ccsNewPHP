<?php

use Illuminate\Http\Request;

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
Route::post("/oauth/token","\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken")->middleware('addClient');


Route::group([
    'prefix' => 'v1',
    'middleware' => ['api']
], function () {
    Route::post("/login", "Login\LoginController@Login");
    Route::post("/createCaptcha", "Common\CaptchaController@generateCaptcha");
    Route::post("/verifyCaptcha", "Common\CaptchaController@verifyCaptcha");
});

Route::group([
   'prefix' => 'v1',
   'middleware' => ['auth:api']
], function (){
    Route::post("/loginInfo","Admin\AdminUsersController@userInfo");
    Route::post("/logout","Login\LoginController@Logout");
    Route::post("/updatePwd","Login\LoginController@updatePwd");
    Route::post("/customers","Admin\CustomerController@customerList");
});
