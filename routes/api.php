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
    Route::post("/agentList","Agent\AgentController@lists");
    Route::post("/agentInfo/{id?}","Agent\AgentController@info");
    Route::post("/agent/create","Agent\AgentController@create");
    Route::post("/agent/agentCreate","Agent\AgentController@agentCreate");
    Route::post("/agent/update/{id}","Agent\AgentController@update");
    Route::post("/user/create","Admin\AdminUsersController@create");
    Route::post("/customers","Admin\CustomerController@customerList");
    Route::post("/addCustomer",'Admin\CustomerController@createCustomer');
    Route::post('/getCustomerInfo',"Admin\CustomerController@getOneCustomer");
    Route::post("/updateBaseInfo","Admin\CustomerController@updateInfo");
    Route::post("/updateCustomerFee","Admin\CustomerController@updateFee");
    Route::post("/CustomerRecharge","Admin\CustomerController@recharge");
    Route::post("/orderList","Admin\CustomerController@orderList");
    Route::post("/customerFlowList","Admin\CustomerController@flowList");
    Route::get("/customerLogin/{id}","Admin\AdminUsersController@customerLogin");
});
