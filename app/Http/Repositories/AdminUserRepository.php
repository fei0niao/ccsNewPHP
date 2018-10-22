<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;
use App\Http\Model\User;

class AdminUserRepository extends BaseRepository
{
    public static function getList($params, $val = '', $query = '')
    {
        if (!$query) $query = User::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params, $val = '', $query = '')
    {
        if (!$query) $query = User::query();
        return BaseRepository::info($id, $params, $val, $query);
    }

    public static function getLoginInfo($user)
    {
        $data['systemParam'] = SystemSettingRepository::getList('agent_id', $user->agent_id);
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