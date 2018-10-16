<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "username" => ["required", "regex:/^1[0-9]{10}$/"],
            "password" => "required"
        ], [
            "username.required" => "手机号不能为空",
            "password.required" => "密码不能为空",
            "username.regex" => "手机号格式不合法",
        ]);
        if ($validator->fails()) {
            return parent::jsonReturn([], parent::CODE_FAIL, $validator->errors()->first());
        }
        $username = $request->get("username");
        $user = User::where('username', $username)->first();
        if (!$user) {
            return parent::jsonReturn([], parent::CODE_FAIL, "用户账号或密码错误");
        }

        if ($user->is_login_forbidden) {
            return parent::jsonReturn([], parent::CODE_FAIL, "账号已被禁用，请联系客服人员！");
        }

        $ret = self::getAuthToken();
        return $ret;
    }

    public static function getAuthToken($username,$password){
        $request = request();
        $request->request->add([
            'grant_type' => "password",
            'client_id' => "2",
            'client_secret' => "cPAZO6gdD6wUt60nCr2p7mQLyfJo6CXTMhBiAThl",
            'username' => $username,
            'password' => $password,
            'scope' => ''
        ]);
        $proxy = Request::create(
            'oauth/token',
            'POST'
        );
        $response = \Route::dispatch($proxy);
    }
}
