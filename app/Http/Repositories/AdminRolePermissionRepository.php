<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;


use App\Http\Model\AdminRolePermission;

class AdminRolePermissionRepository extends BaseRepository
{
    public static function getList($params = [], $val = '', $query = '')
    {
        if (!$query) $query = AdminRolePermission::query()->permission();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = AdminRolePermission::query()->permission();
        return BaseRepository::info($id, $params, $query);
    }
}