<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class AdminRolePermission extends Base
{
    protected $table = "admin_role_permission";

    public function adminPermission()
    {
        return $this->hasMany(AdminPermission::class, 'permission_id', 'id');
    }
}
