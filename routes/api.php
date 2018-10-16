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
    Route::post("/createCaptcha", "Api\Common\CaptchaController@generateCaptcha");
    Route::post("/verifyCaptcha", "Api\Common\CaptchaController@verifyCaptcha");
});
