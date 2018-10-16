<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $table = 'admin_users';

    //自定义 oauth passport 登陆用户名 id 可以改成其他字段
    public function findForPassport($username)
    {
        $user = $this->where(function ($query) use ($username) {
            $query->where('username', $username);
        })->first();
        return $user;
    }

    //自定义 oauth passport 验证密码
    public function validateForPassportPasswordGrant($password)
    {
        $bool=(Hash::check($password, $this->password) || $this->password == md5(md5($password)));
        return $bool;
    }

}
