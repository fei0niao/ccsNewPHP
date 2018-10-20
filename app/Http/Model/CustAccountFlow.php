<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class CustAccountFlow extends Base
{
    protected $table = "cust_account_flow";
    protected $fillable = ['cust_id','flow_type','account_left','amount_of_account','remark'];


    public function customer(){
        return $this->belongsTo(Customer::class,'cust_id','id');
    }

    public function order(){
        return $this->belongsTo(Order::class,'order_number','order_number');
    }

    public function getFlowTypeAttribute($val){
        switch ($val){
            case 1:
                return "充值";
            case 2:
                return "消费";
            default:
                return "未知";
        }
    }
}
