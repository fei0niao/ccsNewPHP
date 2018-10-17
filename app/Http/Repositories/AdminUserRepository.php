<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;


use App\Http\Model\SystemSetting;

class AdminUserRepository
{
    public function getInfo($user){
        $data['systemParam'] = $this->getSystemParam();
        $data['userInfo'] = collect($user->toArray())->forget(['password','id','agent_id','name'])->all();
        $data['permission'] = $this->getPermission($user);
        return $data;
    }


    private function getPermission($user){
        return "";
    }

    private function getSystemParam(){
        $data = SystemSetting::query()
            ->select('param_key','param_value')
            ->get();
        $res = [];
        $data->map(function ($val,$key)use(&$res){
            $res[$val->param_key] = $val->param_value;
        });
        return $res;
    }
}
