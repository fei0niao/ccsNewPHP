<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;


use App\Http\Model\AdminPermission;

class AdminPermissionRepository
{
    public static function getList($params){
        return \Cache::remember(__METHOD__ . json_encode($params), null, function () use ($params) {
            return AdminPermission::querys($params)->get();
        });
    }

    public static function getInfo($params){
        return \Cache::remember(__METHOD__ . json_encode($params), null, function () use ($params) {
            return AdminPermission::querys($params)->first();
        });
    }
}
