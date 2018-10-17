<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Customer extends Base
{
    protected $table = "customer";

    public function Agent(){
        return $this->belongsTo(Agent::class,'agent_id','id');
    }


    public function getIsLoginForbiddenAttribute($val){
        switch ($val){
            case 0:
                return "正常";
            case 1:
                return "禁止登录";
        }
    }

    public function getStatusAttribute($val){
        switch ($val){
            case 0:
                return "正常";
            case 1:
                return "暂停服务";
        }
    }

    public function getServiceFeeAttribute($val){
        return $val * 100 .'%';
    }
}
