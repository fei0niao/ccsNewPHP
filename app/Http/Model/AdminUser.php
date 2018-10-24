<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Base
{
    protected $table = "admin_users";

    //新加字段
    public function getPasswordAttribute()
    {
        return '******';
    }
}
