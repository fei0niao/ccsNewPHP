<?php

namespace App\Http\Controllers\Log;

use App\Http\Repositories\AgentAccountFlowRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\AgentAccountFlow;
use Illuminate\Support\Facades\Auth;

class AgentAccountFlowController extends Controller
{
    public function lists(Request $request)
    {
        $rs = AgentAccountFlowRepository::getList($request->all());
        return jsonReturn($rs);
    }

    public function info($id = '', Request $request)
    {
        if (!$id) $id = (\Auth::User())->agent_id;
        $rs = AgentAccountFlowRepository::getInfo($id, $request->all());
        return jsonReturn($rs);
    }

    public function create(Request $request)
    {
        $fieldAble = ['agent_id', 'flow_type', 'amount_of_account', 'account_left', 'order_number', 'remark'];
        $params = $request->only($fieldAble);
        $validator = AgentAccountFlowRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $rs = AgentAccountFlow::createPermission()->create($params);
        if ($request->returnModel) return jsonReturn($rs);
        return jsonReturn([], '创建成功！');
    }
}
