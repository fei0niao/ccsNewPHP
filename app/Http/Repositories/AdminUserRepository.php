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
        $data['userInfo'] = collect($user->toArray())->forget(['password', 'id', 'agent_id', 'name'])->all();
        $AdminPermissionParams = [
            'adminRolePermission' => ''
        ];
        $data['permission'] = AdminPermissionRepository::getList($AdminPermissionParams);
        //var_dump(\DB::getQueryLog());
        dd($data['permission']->toArray());
        return $data;
    }
}