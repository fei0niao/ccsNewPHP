<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

class AdminUserRepository
{
    public static function getInfo($user)
    {
        $SystemSettingParams = [
            'where' => [
                'agent_id' => $user->agent_id
            ]
        ];
        $data['systemParam'] = SystemSettingRepository::getList($SystemSettingParams);
        $agent = getAgent($user);
        $data['userInfo'] = array_merge(collect($user->toArray())->forget(['password', 'id', 'agent_id', 'name'])->all(),$agent);
        $AdminPermissionParams = [
            'adminRolePermission' => '',
            'has' => [
                'adminRolePermission' => [
                    'where' => ['role_id' => $user->role_id]
                ]
            ]
        ];
        $data['permission'] = AdminPermissionRepository::getList($AdminPermissionParams)->pluck('name')->all();
        return $data;
    }
}
