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

    // strict 严格模式检查所有filedAble 非严格模式只检查params中存在的
    public static function validates($params, $fieldAble, $strict = true)
    {
        $rules = [
            'name' => 'required|unique:agent',
            "contact_phone" => ["required", "regex:/^1[0-9]{10}$/"],
            'fee_rate' => 'required|numeric|between:0,1',
            'remark' => 'alpha_dash|max:200',
            'account_left' => ["required", "regex:/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/"],
        ];
        $messages = [
            'name.required' => "代理商名称错误",
            'name.unique' => "代理商名称已存在",
            "contact_phone.required" => "手机号不能为空",
            "contact_phone.regex" => "请填写正确的手机号码",
            'fee_rate.*' => '费率填写错误',
            'remark.*' => "备注填写错误",
            'account_left.*' => "充值金额错误"
        ];
        return static::makeValidator($params, $fieldAble, $rules, $messages, $strict);
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
