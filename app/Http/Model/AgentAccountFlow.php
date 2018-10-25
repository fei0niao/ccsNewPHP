<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18 0018
 * Time: 20:17
 */

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Repositories\AgentRepository;

class AgentAccountFlow extends Base
{
    protected $table = 'agent_account_flow';

    public static $append_fields = [
    ];

    //权限控制 调用方式 模型名::permission()
    public function scopePermission($query)
    {
        $user = static::getUser();
        if (!$agent_id = $user->agent_id) return $query;
        return $query->where(function ($query) {
            return $query->whereIn('agent_id', AgentRepository::getChildAgent());
        });
    }

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    public function getFeeRateDefAttribute()
    {
        return $this->attributes['fee_rate'] * 100 . '%';
    }

    public function getFlowTypeDefAttribute($val){
        switch ($this->attributes['flow_type']) {
            case 1:
                return '充值';
            case 2:
                return '消费';
            default:
                return '未知';
        }
    }
}
