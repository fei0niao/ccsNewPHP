<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Base
{
    protected $table = "order";
    public function getStatusAttribute($status)
    {
        switch ($status){
            case 0:
                return '已提交';
            case 1:
                return "已完成";
            case 2:
                return "回调失败";
            case 3:
                return "超时未支付";
            case 4:
                return "欠费未回调";
            case 5:
                return "手动回调成功";
            case 6:
                return "手动回调失败";
            default:
                return "未知";
        }
    }

    public function getTypeAttribute($status)
    {
        switch ($status){
            case 1:
                return "支付宝支付";
            case 2:
                return "微信支付";
            default:
                return "未知";
        }
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'merchant_id','merchant_id');
    }
}
