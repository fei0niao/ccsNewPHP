<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class CustAccountFlow extends Base
{
    protected $table = "cust_account_flow";
    protected $fillable = ['cust_id','flow_type','account_left','amount_of_account','remark'];
}
