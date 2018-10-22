<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use App\Http\Model\Agent;

class AgentRepository extends BaseRepository
{
    public static function getList($params, $val = '', $query = '')
    {
        if (!$query) $query = Agent::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params, $val = '', $query = '')
    {
        if (!$query) $query = Agent::query();
        return BaseRepository::info($id, $params, $val, $query);
    }

    public static function validates($params, $fieldAble)
    {
        $rules = [
            'name' => 'required|alpha_dash|max:50',
            "contact_phone" => ["required", "regex:/^1[0-9]{10}$/"],
            'fee_rate' => 'required|numeric|between:0,1',
            'remark' => 'alpha_dash|max:200',
        ];
        $messages = [
            'name.*' => "代理商名称错误",
            "contact_phone.required" => "手机号不能为空",
            "contact_phone.regex" => "请填写正确的手机号码",
            'fee_rate.*' => '费率填写错误',
            'remark.*' => "备注填写错误",
        ];
        return static::makeValidator($params, $fieldAble, $rules, $messages);
    }

    public static function checkFeeRate(Agent $agent)
    {
        return Agent::where(function ($query) use ($agent) {
            return $query->where('agent_id', $agent->parent_id)->where('fee_rate', '<', $agent->fee_rate);
        })->orWhere(function ($query) use ($agent) {
            return $query->where('parent_id', $agent->id)->where('fee_rate', '>', $agent->fee_rate);
        })->first();
    }
}
