<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class AdminOperationLog extends Base
{
    protected $table = "admin_operation_log";
    protected $fillable = ['user_id','path','method','ip','input'];
}
