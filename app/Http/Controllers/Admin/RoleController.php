<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31 0031
 * Time: 15:32
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Model\AdminUser;
use App\Http\Repositories\RoleRespository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function getList(){
        $user = Auth::user();
        try{
            $data = RoleRespository::getList($user);
            return jsonReturn($data);
        }catch (\Exception $exception){
            \Log::info($exception);
            return failReturn('未知错误！请稍后再试');
        }
    }

    public function getOption(){
        $user = Auth::user();
        try{
            $data = RoleRespository::getOption($user);
            return jsonReturn($data);
        }catch (\Exception $exception){
            \Log::info($exception);
            return failReturn('未知错误！请稍后再试');
        }
    }

    public function getAdminInfo(){
        $id = request()->input('id');
        $data = AdminUser::query()->where('id',$id)->first();
        if($data){
            $allow = $data->getOriginal('is_allow_login');
            $data = $data->toArray();
            $data['is_allow_login'] = $allow;
            return jsonReturn($data);
        }else{
            return failReturn('未找到对应用户');
        }
    }

    public function updateAdminInfo(){
        $data = request()->all();
        $res = RoleRespository::update($data);
        if($res){
            return jsonReturn([],'更新后台用户信息成功！');
        } else {
            return failReturn('更新失败！');
        }
    }

    public function createAdmin(Request $request){
        $validate = Validator::make($request->all(),[
            'username' => ['required','unique:admin_users'],
            'password'  => 'required',
            'role_id'   => 'required',
            'agent_id'   => 'required',
            'is_allow_login' => 'required'
        ],[
            'username.required' => '登录名不能为空',
            'username.unique' => '登录名已被占用',
            'password.required' => '密码不能为空',
            'role_id.required'  => '必须选择角色',
            'agent_id.required'  => '必须选择所属代理商',
        ]);
        if ($validate->fails()){
            return failReturn($validate->errors()->first());
        }
        $res = RoleRespository::create($request->all());
        if($res) {
            return jsonReturn([],'新增后台用户成功');
        }else {
            return failReturn('未知错误发生！请稍后再试');
        }
    }
}
