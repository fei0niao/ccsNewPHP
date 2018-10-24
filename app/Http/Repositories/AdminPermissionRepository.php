<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use App\Http\Model\AdminPermission;

class AdminPermissionRepository extends BaseRepository
{
    public static function getList($params, $val = '', $query = '')
    {
        if (!$query) $query = AdminPermission::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = AdminPermission::query();
        return BaseRepository::info($id, $params, $query);
    }

    public static function getPermissions($user = '')
    {
        $user = $user ?: static::getUser();
        $where = [
            'where' => [
                'role_id' => $user->role_id,
            ],
            'whereIn' => [
                'agent_id' => [0, $user->agent_id]
            ]
        ];
        $permission_ids = AdminRolePermissionRepository::getList($where)->pluck('permission_id')->all();
        return self::getList('id', $permission_ids)->pluck('name')->all();
    }
}
