<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31 0031
 * Time: 16:19
 */

namespace App\Http\Repositories;


use App\Http\Model\AdminRole;
use App\Http\Model\AdminUser;
use App\Http\Model\Agent;
use App\Http\Model\User;
use Illuminate\Support\Facades\Hash;

class RoleRespository
{
    static public function getList($user){
        // 1 总管理员  2 独立管理员  3 某独立
        $is_admin = $user->agent_id == null ? true : false;
        $list = AdminUser::query();
        $query = request()->all();
        $limt = $query['limit'];
        if (!$is_admin && $user->agent_id = 1) {
            $list = $list->where('agent','>=', 1);
        }
        if (!$is_admin && $user->agent_id > 1) {
            $agents = self::getAgents($user,['id']);
            $agents = array_dot($agents);
            $list = $list->whereIn('agent_id',$agents);
        }
        if (isset($query['search']['role_id']) && $query['search']['role_id'] != ""){
            $list = $list->where('role_id', $query['search']['role_id']);
        }
        if (isset($query['search']['agent_id']) && $query['search']['agent_id'] != ""){
            $list = $list->where('agent_id', $query['search']['agent_id']);
        }
        if (isset($query['search']['name']) && $query['search']['name'] != ""){
            $list = $list->where('username', $query['search']['name']);
        }
        $list = $list->with(['role' => function($query){
            $query->select('id','role_name');
        }])->with(['agent' => function($query){
            $query->select('id','name');
        }]);
        $list = $list->orderBy('id',"DESC")->paginate($limt);
        $formatData['total'] = $list->total();
        array_map(function ($val)use(&$formatData){
            $val = $val->toArray();
            $formatData['data'][] = array_dot($val);
        },$list->items());
        unset($data);
        return $formatData;
    }

    static public function getOption($user){
        $role = AdminRole::query()->select(['id','role_name'])->orderBy('id',"ASC")->get()->toArray();
        $agents = [];
        $canSeeAll = $user->agent_id == null || $user->agent_id == 1 ? true : false;
        if ($canSeeAll) {
            $agents = Agent::query()->orderBy('id',"ASC")->get(['id','name'])->toArray();
        } else {
            $agents = self::getAgents($user,['id','name']);
        }
        $data = compact('role','agents');
        return $data;
    }

    static private function getAgents($user, array $select){
        $selfAgent = Agent::query()->where('id',$user->agent_id)->first();
        $agents = Agent::query()
            ->where("relation",'like',$selfAgent->relation . $user->agent_id .'%')
            ->orWhere('id',$user->agent_id)
            ->orderBy('id',"ASC")
            ->get($select)
            ->toArray();
        return $agents;
    }

    static public function update($data){
        $user = AdminUser::query()->where('id',$data['id'])->first();
        if(!$user){
            return false;
        }
        if($data['password'] != ''){
            $user->password = Hash::make($data['password']);
        }
        $user->role_id = $data['role_id'];
        $user->is_allow_login = $data['is_allow_login'];
        $res = $user->save();
        return $res;
    }

    static public function create($data){
        $data['name'] = $data['username'];
        $data['password'] = Hash::make($data['password']);
        $res = AdminUser::create($data);
        return $res;
    }
}
