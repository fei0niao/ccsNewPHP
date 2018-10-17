<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17 0017
 * Time: 15:17
 */

namespace App\Http\Repositories;


use App\Http\Model\Agent;
use App\Http\Model\Customer;

class CustomerRepository
{
    static function getCustomerList($user){
        $limt = request()->input('limit');
        $agentId = $user->agent_id;
        $agents = self::getAgents($agentId);
        $list = Customer::query();
        if(request()->input('canLogin')){
            $list = $list->where('is_login_forbidden',request()->input('canLogin'));
        }
        if(request()->input('useStatus')){
            $list = $list->where('status',request()->input('useStatus'));
        }
        if(request()->input('name')){
            $list = $list->where('name','like','%'.request()->input('name').'%');
        }
        if(request()->input('id')){
            $list = $list->where('id',request()->input('id'));
        }
        if($agents){
            $list = $list->where('agent_id','in',$agents);
        }
        $data=$list->with(['Agent' => function($query){
             $query->select('id','name');
        }])
            ->paginate($limt);
        $formatData['total'] = $data->total();
        array_map(function ($val)use(&$formatData,$agentId){
            $val = $val->toArray();
            if(!$agentId){
                $val['canPlay'] = true;
            }
            if(!$agentId || $agentId == $val['agent']['id']){
                $val['canRecharge'] = true;
                $val['canChange'] = true;
            }
            $formatData['data'][] = array_dot($val);
        },$data->items());
        unset($data);
        return $formatData;
    }

    private static function getAgents($agentId){
        if($agentId == "" || $agentId == 1){
            return [];
        }else{
            $selfAgent = Agent::query()->find($agentId)->get();
            $agents = Agent::query()
                ->where("relation",'like',$selfAgent->getSelfRelationAttribute())
                ->get('id')
                ->toArray();
            return $agents;
        }
    }
}
