<?php

namespace App\Http\Controllers\Admin;

use App\Http\Repositories\AdminUserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\AdminUser;
use Illuminate\Support\Facades\Auth;

class AdminUsersController extends Controller
{
    public function userInfo(Request $request)
    {
        $user = Auth::user();
        try{
            $data = AdminUserRepository::getLoginInfo($user);
            return jsonReturn($data);
        }catch (\Exception $exception){
            dd($exception);
            return failReturn("未知错误发生！请稍后再试！");
        }
    }

    public function rolePlay($id = '', Request $request)
    {
        $user = AdminUser::find($id);
        $access_token = $user->createToken('rolePlay')->accessToken;
        return jsonReturn(compact('access_token'));
    }


    public function create(Request $request)
    {
        $data =  $request->all();
        return AdminUserRepository::create($data);
    }
}
