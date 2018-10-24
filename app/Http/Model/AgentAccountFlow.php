<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18 0018
 * Time: 20:17
 */

namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class AgentAccountFlow extends Base
{
    protected $table = 'agent_account_flow';

    public static $append_fields = [
    ];

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
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
