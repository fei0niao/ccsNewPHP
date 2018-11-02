<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Base
{
    protected $table = "admin_users";

    protected $fillable= ['username','name','is_allow_login','password','agent_id','role_id'];

    //新加字段
    public function getPasswordAttribute()
    {
        return '******';
    }

    public function role(){
        return $this->belongsTo(AdminRole::class, 'role_id', 'id');
    }

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    public function getIsAllowLoginAttribute($value){
        switch ($value) {
            case 0:
                return '禁止登陆';
            case 1:
                return '正常';
        }
    }
}
