<?php

namespace App\Http\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
    protected $table = "admin_users";

    public static $append_fields = [
    ];
    public static $_fields = [];

    //belongs to角色
    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'id');
    }

    //belongs to代理商
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }


    public function adminRolePermission()
    {
        return $this->hasMany(AdminRolePermission::class, 'agent_id', 'agent_id');
    }

    //新加字段
    public function getNewIsForbidAttribute()
    {
        switch ($this->attributes['is_forbid']) {
            case 0:
                return '正常';
            case 1:
                return '禁用';
            default:
                return '未知';
        }
    }

    //自定义 oauth passport 登陆用户名 id 可以改成其他字段
    public function findForPassport($username)
    {
        $user = $this->where('username', $username)->first();
        return $user;
    }

    //自定义 oauth passport 验证密码
    public function validateForPassportPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }

    public function oauthAccessToken()
    {
        return $this->hasMany('\App\OauthAccessToken');
    }
}

