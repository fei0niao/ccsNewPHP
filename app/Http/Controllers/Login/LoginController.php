<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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
            return failReturn($validator->errors()->first());
        }
        $username = $request->get("username");
        $password = $request->get("password");
        $user = User::where('username', $username)->first();
        if (!$user) {
            return failReturn("用户账号或密码错误!");
        }
        if (!$user->is_allow_login) {
            return failReturn("账号已被禁用，请联系客服人员！");
        }
        $agent = $user->agent;
        if($agent && !$agent->is_allow_login) return failReturn("代理商已被禁用，请联系客服人员！");
        $ret = self::getAuthToken($username, $password);
        if(!$ret) return failReturn("用户账号或密码错误!");
        return jsonReturn($ret);
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
        $ret = json_decode(\Route::dispatch($proxy)->getContent(), true);
        if (!$ret || !isset($ret['access_token'])) {
            return false;
        }
        return $ret;
    }

    public function logout(Request $request){
        //注销token
        $jwtInfo = parsePassportAuthorization($request);
        if ($jwtInfo) {
            $jti = $jwtInfo["jti"];
            DB::table("oauth_access_tokens")->where("id", $jti)->delete();
            DB::table("oauth_refresh_tokens")->where("access_token_id", $jti)->delete();
        }

        return jsonReturn([]);
    }

    public function updatePwd(Request $request){
        $validator =  Validator::make($request->all(),[
            'oldPass' => 'required',
            'newPass' => 'required',
            'rePass' => 'required',
        ],[
            'oldPass.required' => "必须填写原密码",
            'newPass.required' => "必须填写新密码",
            'rePass.required' => "必须填写新密码",
        ]);
        if($validator->fails()){
            return failReturn($validator->errors()->first());
        }
        if($request->input("newPass") === $request->input("oldPass")){
            return failReturn("原密码与修改后密码一致");
        }
        if($request->input("newPass") !== $request->input("rePass")){
            return failReturn("两次密码不一致");
        }
        if(!Hash::check($request->input('oldPass'), Auth::user()->password)){
            return failReturn("原密码错误");
        }
        $user = Auth::user();
        $user->password = Hash::make($request->input('newPass'));
        $user->save();
        return jsonReturn([],'更新密码成功');
    }
}
