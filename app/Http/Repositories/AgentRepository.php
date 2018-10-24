<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use App\Http\Model\Agent;
use Illuminate\Support\Facades\Auth;

class AgentRepository extends BaseRepository
{
    public static function getList($params = [], $val = '', $query = '')
    {
        if (!$query) $query = Agent::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = Agent::query();
        return BaseRepository::info($id, $params, $query);
    }

    public static function create($data, $returnModel = false, $flow = [])
    {
        $fieldAble = ['parent_id', 'level', 'relation', 'name', 'contact_person', 'contact_phone', 'fee_rate', 'remark', 'account_left'];
        $params = filterArray($data, $fieldAble);
        $validator = AgentRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if ($params['fee_rate'] < static::getUserAgent()->fee_rate) {
            return failReturn('代理商费率不能小于其父级！');
        }
        $rs = Agent::createPermission()->create($params);
        if (!$rs) return failReturn('创建失败1！');
        //有充值时创建流水
        if (!empty($params['account_left'])) {
            $arr = [
                'agent_id' => $rs->id,
                'account_left' => $params['account_left'],
                'amount_of_account' => $params['account_left'],
                'flow_type' => 1,
                'fee_rate' => $rs->fee_rate,
                'remark' => '创建代理商时的充值'
            ];
            $ret = AgentAccountFlowRepository::create($arr + $flow);
            if (!$ret['status']) return $ret;
        }
        if ($returnModel) return jsonReturn($rs);
        return jsonReturn([], '创建成功！');
    }

    public static function update($id, $data, $returnModel = false, $flow = [])
    {
        $fieldAble = ['name', 'contact_person', 'contact_phone', 'fee_rate', 'remark', 'account_left', 'account_left_','is_allow_login'];
        $params = filterArray($data, $fieldAble);
        $agent = Agent::updatePermission()->find($id);
        $origAgent = clone $agent;
        if (!$agent) return failReturn('资源不存在！');
        if (!empty($params['account_left_'])) {
            $params['account_left'] = $agent->account_left + $params['account_left_'];
            unset($params['account_left_']);
        }
        $validator = AgentRepository::validates($params, $fieldAble, false, $id);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if (isset($params['fee_rate'])) {
            $checkFeeRate = AgentRepository::checkFeeRate($agent);
            if ($checkFeeRate) return failReturn('代理商费率超过其父级或低于其子级,请重新调整！');
        }
        $ret = $agent->update($params);
        if (!$ret) return failReturn('更新失败！');
        if (isset($params['account_left']) && $agent->account_left != $origAgent->account_left) {
            $amount_of_account = $agent->account_left - $origAgent->account_left;
            $arr = [
                'agent_id' => $agent->id,
                'account_left' => $agent->account_left,
                'amount_of_account' => $amount_of_account,
                'fee_rate' => $agent->fee_rate
            ];
            $ret = AgentAccountFlowRepository::create($arr + $flow);
            if (!$ret['status']) return $ret;
        }
        if ($returnModel) return jsonReturn(['orig' => $origAgent, 'new' => $agent]);
        return jsonReturn([], '更新成功！');
    }

    // strict 严格模式检查所有filedAble 非严格模式只检查params中存在的
    public static function validates($params, $fieldAble, $strict = true, $id = '')
    {
        $rules = [
            'name' => $id ? 'required|unique:agent,name,' . $id : 'required|unique:agent',
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
            return $query->where('id', $agent->parent_id)->where('fee_rate', '>', $agent->fee_rate);
        })->orWhere(function ($query) use ($agent) {
            return $query->where('parent_id', $agent->id)->where('fee_rate', '<', $agent->fee_rate);
        })->first();
    }

    public static function getParentAgent(Agent $agent)
    {
        //todo 加缓存
        return Agent::find($agent->parent_id);
    }
}
