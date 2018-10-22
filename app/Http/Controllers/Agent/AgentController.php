<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\Agent;
use App\Http\Repositories\AgentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class AgentController extends Controller
{
    public function lists(Request $request)
    {
        $rs = AgentRepository::getList($request->all());
        return jsonReturn($rs);
    }

    public function info($id = '', Request $request)
    {
        if (!$id) $id = (\Auth::User())->agent_id;
        $rs = AgentRepository::getInfo($id, $request->all());
        return jsonReturn($rs);
    }

    public function create(Request $request)
    {
        $fieldAble = ['parent_id', 'level', 'relation', 'name', 'contact_person', 'contact_phone', 'fee_rate', 'remark', 'account_left'];
        $params = $request->only($fieldAble);
        $validator = AgentRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if ($params['fee_rate'] < static::getUserAgent()->fee_rate) {
            return failReturn('代理商费率不能小于其父级！');
        }
        $rs = Agent::createPermission()->create($params);
        if (!$rs) return failReturn('创建失败！');
        if ($request->returnModel) return jsonReturn($rs);
        return jsonReturn([], '创建成功！');
    }

    public static function agentCreate(Request $request)
    {
        $userAgent = static::getUserAgent();
        $user = $request->user;
        $agent = $request->agent;
        DB::beginTransaction();
        try {
            if (!$user && !$agent) return failReturn('请求参数错误！');
            $arr = [
                'parent_id' => $userAgent->id,
                'level' => $userAgent->level + 1,
                'relation' => $userAgent->relation . '_' . $userAgent->id
            ];
            $ret = dispatchRoute('v1/agent/create', $agent + $arr, true);
            if (!$ret['status']) return $ret;
            $agent = $ret['data'];
            /*---------------------------------------------*/
            $arr = [
                'role_id' => 11,
                'agent_id' => $agent['id'],
                'name' => $user['username']
            ];
            $ret = dispatchRoute('v1/user/create', $user + $arr);
            if (!$ret['status']) return $ret;
            /*---------------------------------------------*/
            if ($agent['account_left']) {
                $arr = [
                    'agent_id' => $agent['id'],
                    'flow_type' => 1,
                    'amount_of_account' => $agent['account_left'],
                    'account_left' => $agent['account_left'],
                    'remark' => '创建代理商时的充值'
                ];
                $ret = dispatchRoute('v1/agentAccountFlow/create', $arr);
                if (!$ret['status']) return $ret;
                $arr = [
                    'agent_id' => $userAgent->id,
                    'flow_type' => 2,
                    'amount_of_account' => -$agent['account_left'],
                    'account_left' => round2($userAgent->account_left - $agent['account_left'] * (($userAgent->fee_rate) / $agent['fee_rate'])),
                    'remark' => '创建代理商时的扣费'
                ];
                $ret = dispatchRoute('v1/agentAccountFlow/create', $arr, true);
                if (!$ret['status']) return $ret;
                $arr = [
                    'account_left' => $ret['data']['account_left']
                ];
                $ret = dispatchRoute('v1/agent/update/' . $userAgent->id, $arr);
                if (!$ret['status']) return $ret;
            }
            /*---------------------------------------------*/
            DB::commit();
            return jsonReturn([], '创建成功！');
        } catch (\Exception $e) {
            \Log::info($e);
            DB::rollBack();
            return failReturn('创建失败！');
        }
    }

    public function update($id, Request $request)
    {
        $fieldAble = ['name', 'contact_person', 'contact_phone', 'fee_rate', 'remark', 'account_left'];
        $params = $request->only($fieldAble);
        $validator = AgentRepository::validates($params, $fieldAble, false);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $rs = Agent::updatePermission()->find($id);
        if (!$rs) return failReturn('资源不存在！');
        if (isset($params['fee_rate'])) {
            $checkFeeRate = !AgentRepository::checkFeeRate($rs);
            if (!$checkFeeRate) return failReturn('代理商费率超过其父级或低于其子级,请重新调整！');
        }
        $rs = $rs->update($params);
        if (!$rs) return failReturn('更新失败！');
        return jsonReturn([], '更新成功！');
    }
}