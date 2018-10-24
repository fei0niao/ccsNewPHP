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
        $data =  $request->all();
        return AgentAccountFlowRepository::create($data);
    }
}
