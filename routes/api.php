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
    Route::post("/login", "Login\LoginController@Login")->middleware("checkCode");
    Route::post("/createCaptcha", "Common\CaptchaController@generateCaptcha");
    Route::post("/verifyCaptcha", "Common\CaptchaController@verifyCaptcha");
});

Route::group([
   'prefix' => 'v1',
   'middleware' => ['auth:api']
], function (){
    Route::post("/loginInfo","Admin\AdminUsersController@userInfo");
    Route::post("/adminUser/rolePlay/{id}","Admin\AdminUsersController@rolePlay");

    Route::post("/logout","Login\LoginController@Logout");
    Route::post("/updatePwd","Login\LoginController@updatePwd");

    Route::post("/agentList","Agent\AgentController@lists");
    Route::post("/agent/rolePlay/{id}","Agent\AgentController@rolePlay");
    Route::post("/agentInfo/{id?}","Agent\AgentController@info");
    Route::post("/agent/agentCreate","Agent\AgentController@agentCreate");
    Route::post("/agent/infoUpdate/{id}","Agent\AgentController@infoUpdate");
    Route::post("/agent/feeRateUpdate/{id}","Agent\AgentController@feeRateUpdate");
    Route::post("/agent/accountUpdate/{id}","Agent\AgentController@accountUpdate");
    Route::post("/agentAccountFlowList","Log\AgentAccountFlowController@lists");
    Route::post("/agentAccountFlow/create","Log\AgentAccountFlowController@create");

    Route::post("/user/create","Admin\AdminUsersController@create");
    Route::post("/customers","Admin\CustomerController@customerList");
    Route::post("/addCustomer",'Admin\CustomerController@createCustomer');
    Route::post('/getCustomerInfo',"Admin\CustomerController@getOneCustomer");
    Route::post("/updateBaseInfo","Admin\CustomerController@updateInfo");
    Route::post("/updateCustomerFee","Admin\CustomerController@updateFee");
    Route::post("/CustomerRecharge","Admin\CustomerController@recharge");
    Route::post("/orderList","Admin\CustomerController@orderList");
    Route::post("/customerFlowList","Admin\CustomerController@flowList");
    Route::post("/customerLogin/{id}","Admin\CustomerController@customerLogin");
});
