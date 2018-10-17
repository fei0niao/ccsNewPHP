<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Base
{
    protected $table = "admin_permission";

    public function adminRolePermission()
    {
        return $this->belongsTo(AdminRolePermission::class, 'permission_id');
    }
}