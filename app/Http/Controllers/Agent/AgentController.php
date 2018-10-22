<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\Agent;
use App\Http\Repositories\AgentRepository;
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
        $fieldAble = ['name','contact_person','contact_phone','fee_rate','remark'];
        $params = $request->only($fieldAble);
        $validator = AgentRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $user = \Auth::user();
        $agent = $user->agent;
        if ($params['fee_rate'] > $user->agent->fee_rate) {
            return failReturn('代理商费率超过其父级！');
        }
        $arr = [
            'parent_id' => $agent->id,
            'level' => $agent->level + 1,
            'relation' => $agent->relation . '_' . $agent->id
        ];
        $params += $arr;
        $rs = Agent::createPermission()->create($params);
        if (!$rs) return failReturn('创建失败！');
        if ($request->returnModel) return modelReturn($rs);
        return jsonReturn([], '创建成功！');
    }

    public static function agentCreate(Request $request)
    {
        if(!$request->user && !$request->agent) return failReturn('请求参数错误！');
        $request->request->add($request->agent, ['returnModel'=> true]);
        $proxy = Request::create(
            'v1/agent/create',
            'POST'
        );
        $ret = json_decode(\Route::dispatch($proxy)->getContent(), true);
        if(!$ret['status']) return $ret;
        $arr = [
            'role_id' => 11,
            'agent_id' => $ret['data']->id
        ];
        $request->request->add($request->user + $arr);
        $proxy = Request::create(
            'v1/user/create',
            'POST'
        );
        $ret = json_decode(Route::dispatch($proxy)->getContent(), true);
        if(!$ret['status']) return $ret;
        return jsonReturn([], '创建成功！');
    }

    public function update($id, Request $request)
    {
        $fieldAble = ['name','contact_person','contact_phone','fee_rate','remark'];
        $params = $request->only($fieldAble);
        $validator = AgentRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $rs = Agent::updatePermission()->find($id);
        if (!$rs) return failReturn('资源不存在！');
        $checkFeeRate = !AgentRepository::checkFeeRate($rs);
        if (!$checkFeeRate) return failReturn('代理商费率超过其父级或低于其子级,请重新调整！');
        $rs = $rs->update($params);
        if (!$rs) return failReturn('更新失败！');
        return jsonReturn([], '更新成功！');
    }
}