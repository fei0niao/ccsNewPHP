<?php

namespace App\Http\Controllers\Agent;

use App\Http\Repositories\AdminUserRepository;
use App\Http\Repositories\SystemSettingRepository;
use App\Http\Repositories\UserRepository;
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

    public function rolePlay($id = '', Request $request)
    {
        $user = UserRepository::getAdminByAgentID($id);
        $access_token = $user->createToken('rolePlay')->accessToken;
        $admin_url = SystemSettingRepository::getParamValue('admin_url');
        return jsonReturn(compact('access_token','admin_url'));
    }

    public static function agentCreate(Request $request)
    {
        $userAgent = static::getUserAgent();
        $user = $request->user;
        $agent = $request->agent;
        if (!$user && !$agent) return failReturn('请求参数错误！');
        DB::beginTransaction();
        try {
            $arr = [
                'parent_id' => $userAgent->id,
                'level' => $userAgent->level + 1,
                'relation' => $userAgent->relation . '_' . $userAgent->id
            ];
            $ret = AgentRepository::create($agent + $arr, true);
            if (!$ret['status']) return $ret;
            $agent = $ret['data'];
            /*---------------------------------------------*/
            $arr = [
                'role_id' => 1,
                'agent_id' => $agent['id'],
                'name' => $user['username']
            ];
            $ret = AdminUserRepository::create($user + $arr);
            if (!$ret['status']) return $ret;
            DB::commit();
            return jsonReturn([], '创建成功！');
        } catch (\Exception $e) {
            \Log::info($e);
            DB::rollBack();
            return failReturn('创建失败3！');
        }
    }

    public static function infoUpdate($id, Request $request)
    {
        $user = $request->user;
        $agent = $request->agent;
        if (!$user || !$agent) return failReturn('请求参数错误！');
        DB::beginTransaction();
        try {
            $ret = AgentRepository::update($id, $agent);
            if (!$ret['status']) return $ret;
            //获取管理员信息
            $adminUser = AdminUserRepository::getAdminByAgentID($id);
            $ret = AdminUserRepository::update($adminUser->id, $user);
            if (!$ret['status']) return $ret;
            DB::commit();
            return jsonReturn([], '更新成功！');
        } catch (\Exception $e) {
            \Log::info($e);
            DB::rollBack();
            return failReturn('更新失败！');
        }
    }

    public static function feeRateUpdate($id, Request $request)
    {
        $agent = $request->only('fee_rate');
        DB::beginTransaction();
        try {
            $ret = AgentRepository::update($id, $agent);
            if (!$ret['status']) return $ret;
            DB::commit();
            return jsonReturn([], '更新成功！');
        } catch (\Exception $e) {
            \Log::info($e);
            DB::rollBack();
            return failReturn('更新失败！');
        }
    }

    public static function accountUpdate($id, Request $request)
    {
        $params = $request->only('account_left_');
        DB::beginTransaction();
        try {
            $ret = AgentRepository::update($id, $params);
            if (!$ret['status']) return $ret;
            DB::commit();
            return jsonReturn([], '充值成功！');
        } catch (\Exception $e) {
            \Log::info($e);
            DB::rollBack();
            return failReturn('充值失败！');
        }
    }
}