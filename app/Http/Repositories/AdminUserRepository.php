<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;
use App\Http\Model\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminUserRepository extends BaseRepository
{
    public static function getList($params, $val = '', $query = '')
    {
        if (!$query) $query = AdminUser::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = AdminUser::query();
        return BaseRepository::info($id, $params, $query);
    }

    public static function getAdminByAgentID($agent_id)
    {
        return self::getInfo(['agent_id' => $agent_id, 'role_id' => 1]);
    }

    public static function create($data, $returnModel = false)
    {
        $fieldAble = ['agent_id','username','password','name','avatar','is_allow_login','role_id'];
        $params = filterArray($data, $fieldAble);
        $validator = AdminUserRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if(isset($params['password'])) $params['password'] = Hash::make($params['password']);
        $rs = AdminUser::createPermission()->create($params);
        if (!$rs) return failReturn('创建失败2！');
        if ($returnModel) return jsonReturn($rs);
        return jsonReturn([], '创建成功！');
    }

    public static function update($id, $data, $returnModel = false)
    {
        $fieldAble = ['agent_id','username','password','name','avatar','is_allow_login','role_id'];
        $params = filterArray($data, $fieldAble);
        $adminUser = AdminUser::updatePermission()->find($id);
        if (!$adminUser) return failReturn('资源不存在！');
        $validator = AgentRepository::validates($params, $fieldAble, false);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if(isset($params['password'])) $params['password'] = Hash::make($params['password']);
        $ret = $adminUser->update($params);
        if (!$ret) return failReturn('更新失败！');
        if ($returnModel) return jsonReturn($adminUser);
        return jsonReturn([], '更新成功！');
    }

    public static function getLoginInfo($user)
    {
        $data['systemParam'] = SystemSettingRepository::getParamValue();
        $agent = getAgent($user);
        $data['userInfo'] = collect($user->toArray())->forget(['password', 'id', 'agent_id', 'name'])->all() + $agent;
        $permission_ids = $user->adminRolePermission->pluck('permission_id')->all();
        $data['permission'] = AdminPermissionRepository::getList('id',$permission_ids)->pluck('name')->all();
        return $data;
    }

    // strict 严格模式检查所有filedAble 非严格模式只检查params中存在的
    public static function validates($params, $fieldAble, $strict = true)
    {
        $rules = [
            'username' => 'required|unique:admin_users'
        ];
        $messages = [
            'username.required' => "登录账号错误",
            'username.unique' => "登录账号已存在"
        ];
        return static::makeValidator($params, $fieldAble, $rules, $messages, $strict);
    }
}