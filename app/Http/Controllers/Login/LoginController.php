<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "username" => ["required"],
            "password" => "required"
        ], [
            "username.required" => "用户名不能为空",
            "password.required" => "密码不能为空"
        ]);
        if ($validator->fails()) {
            return parent::jsonReturn([], parent::CODE_FAIL, $validator->errors()->first());
        }
        $username = $request->get("username");
        $password = $request->get("password");
        $user = User::where('username', $username)->first();
        if (!$user) {
            return parent::jsonReturn([], parent::CODE_FAIL, "用户账号或密码错误!");
        }

        if (!$user->is_allow_login) {
            return parent::jsonReturn([], parent::CODE_FAIL, "账号已被禁用，请联系客服人员！");
        }

        $ret = self::getAuthToken($username, $password);
        return $ret;
    }

    public static function getAuthToken($username, $password)
    {
        $request = request();
        $request->request->add([
            'grant_type' => "password",
            'client_id' => config("env.CLIENT_ID"),
            'client_secret' => config("env.CLIENT_SECRET"),
            'username' => $username,
            'password' => $password,
            'scope' => ''
        ]);
        $proxy = Request::create(
            'oauth/token',
            'POST'
        );
        return $response = \Route::dispatch($proxy);
    }
}
