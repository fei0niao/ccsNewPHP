<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17 0017
 * Time: 15:17
 */

namespace App\Http\Repositories;


use App\Http\Model\Agent;
use App\Http\Model\CustAccountFlow;
use App\Http\Model\Customer;
use App\Http\Model\Order;

class CustomerRepository
{
    static function getCustomerList($user){
        $limt = request()->input('limit');
        $agentId = $user->agent_id;
        $requset = request()->all();
        $agents = self::getAgents($agentId, 'id');
        $list = Customer::query();
        if($requset['search']['canLogin'] !== null){
            $list = $list->where('is_login_forbidden',$requset['search']['canLogin']);
        }
        if($requset['search']['useStatus'] !== null){
            $list = $list->where('status',$requset['search']['useStatus']);
        }
        if($requset['search']['name']){
            $list = $list->where('name','like','%'.$requset['search']['name'].'%');
        }
        if($requset['search']['id']){
            $list = $list->where('id',$requset['search']['id']);
        }
        if($agents){
            $list = $list->where('agent_id','in',$agents);
        }
        $list=$list->orderBy('id',"DESC")
            ->with(['Agent' => function($query){
             $query->select('id','name');
        }]);
        if(request()->has('offset')){
            $data = self::excelData($list,$limt);
            return $data;
        }else{
            $data = $list->paginate($limt);
        }
        $formatData['total'] = $data->total();
        array_map(function ($val)use(&$formatData,$agentId){
            $val = $val->toArray();
            if(!$agentId){
                $val['canPlay'] = true;
            }
            if($agentId == $val['agent']['id']){
                $val['canRecharge'] = true;
                $val['canChange'] = true;
            }
            $formatData['data'][] = array_dot($val);
        },$data->items());
        unset($data);
        return $formatData;
    }

    static public function getOrderList($user){
        $limt = request()->input('limit');
        $request = request()->all();
        $agentId = $user->agent_id;
        $agents = self::getAgents($agentId, 'merchant_id');
        $list = Order::query();
        if($request['search']['id']){
            $id = $request['search']['id'];
            $list = $list->whereHas('customer', function($query)use($id){
                $query->where('id',$id);
            });
        }
        if($request['search']['name']){
            $name =$request['search']['name'];
            $list = $list->whereHas('customer', function($query)use($name){
                $query->where('name',$name);
            });
        }
        $list = $list->with(['customer' => function($query){
            $query->select("merchant_id",'name','id');
        }]);
        if($request['search']['orderNum']){
            $orderNum = $request['search']['orderNum'];
            $list = $list->where(function($query)use($orderNum){
                return $query->where("trade_order_id",$orderNum)->orWhere("order_number",$orderNum);
            });
        }
        if($request['search']['status'] !== null){
            $list = $list->where('status',$request['search']['status']);
        }
        if($agents){
            $list = $list->where('merchant_id','in',$agents);
        }
        $list=$list->orderBy('id',"DESC");
        // 有offset 认为为导出
        if(request()->has('offset')){
            $data = self::excelData($list,$limt);
            return $data;
        }else{
            $data = $list->paginate($limt);
        }
        $formatData['total'] = $data->total();
        array_map(function ($val)use(&$formatData,$agentId){
            $val = $val->toArray();
            $formatData['data'][] = array_dot($val);
        },$data->items());
        unset($data);
        return $formatData;
    }

    static public function getFlowList($user){
        $limt = request()->input('limit');
        $request = request()->all();
        $agentId = $user->agent_id;
        $agents = self::getAgents($agentId, 'id');
        $list = CustAccountFlow::query();
        if($request['search']['id']){
            $id = $request['search']['id'];
            $list = $list->whereHas('customer', function($query)use($id){
                $query->where('id',$id);
            });
        }
        if($request['search']['name']){
            $name =$request['search']['name'];
            $list = $list->whereHas('customer', function($query)use($name){
                $query->where('name',$name);
            });
        }
        $list = $list->with(['customer' => function($query){
            $query->select('name','id');
        }]);
        if($request['search']['order_number']){
            $order_number = $request['search']['order_number'];
            $list = $list->where("order_number",$order_number);
        }
        if($request['search']['flow_type'] !== null){
            $list = $list->where('flow_type',$request['search']['flow_type']);
        }
        if($agents){
            $list = $list->whereHas('customer', function($query)use($agents){
                $query->where('agent_id','in',$agents);
            });
        }
        $list = $list->with(['order' => function($query){
            $query->select('order_number','trade_order_id','amount');
        }]);
        $list=$list->orderBy('id',"DESC");
        // 有offset 认为为导出
        if(request()->has('offset')){
            $data = self::excelData($list,$limt);
            return $data;
        }else{
            $data = $list->paginate($limt);
        }
        $formatData['total'] = $data->total();
        array_map(function ($val)use(&$formatData,$agentId){
            $val = $val->toArray();
            $formatData['data'][] = array_dot($val);
        },$data->items());
        unset($data);
        return $formatData;
    }


    private static function getAgents($agentId, $col){
        if($agentId == "" || $agentId == 1){
            return [];
        }else{
            $selfAgent = Agent::query()->find($agentId)->get();
            $agents = Agent::query()
                ->where("relation",'like',$selfAgent->getSelfRelationAttribute())
                ->get($col)
                ->toArray();
            return $agents;
        }
    }
    static private function excelData($list,$limit){
        $data = $list->offset(request()->input('offset'))->limit($limit)->get()->toArray();
        $formatData = [];
        array_map(function ($val)use(&$formatData){
            $formatData['list'][] = array_dot($val);
        },$data);
        unset($data);
        return $formatData;
    }
}