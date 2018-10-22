<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Base
{
    protected $table = 'action_logs';
    protected $fillable = ["ip",'user_id','oldData','newData','remark'];
}
