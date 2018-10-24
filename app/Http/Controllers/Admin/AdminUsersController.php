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

    public function create(Request $request)
    {
        $fieldAble = ['agent_id','username','password','name','avatar','is_allow_login','role_id'];
        $params = $request->only($fieldAble);
        $validator = AdminUserRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $rs = AdminUser::createPermission()->create($params);
        if (!$rs) return failReturn('创建失败！');
        if ($request->returnModel) return jsonReturn($rs);
        return jsonReturn([], '创建成功！');
    }
}
