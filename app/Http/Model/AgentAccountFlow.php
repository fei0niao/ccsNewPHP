<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18 0018
 * Time: 20:17
 */

namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class AgentAccountFlow extends Model
{
    protected $table = 'agent_account_flow';
    protected $fillable = ['cust_id','flow_type','amount_of_account','account_left','remark'];
}
