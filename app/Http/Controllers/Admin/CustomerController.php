<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17 0017
 * Time: 15:15
 */

namespace App\Http\Controllers\Admin;


use App\Http\Model\Agent;
use App\Http\Model\Customer;
use App\Http\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerController
{
    public function customerList(){
        $user = Auth::user();
        try{
            $list = CustomerRepository::getCustomerList($user);
            return jsonReturn($list);
        }catch (\Exception $exception){
            dd($exception);
            return failReturn("未知错误发生！请稍后再试！");
        }
    }

    public function createCustomer(Request $request){
        $validate = Validator::make($request->all(),[
            'name' => ['required','unique:customer'],
            'userName' => 'required',
            'cellphone' => ['required','unique:customer'],
            'loginName' => ['required','unique:customer'],
            'password'  => 'required',
            'service_fee' => 'required',
            'cust_capital_amount' => 'min:0'
        ],[
            'name.required' => '用户名不能为空',
            'name.unique' => '商户名已被占用',
            'userName.required' => '联系人不能为空',
            'cellphone.required' => '联系人不能为空',
            'cellphone.unique' => '该手机号已被商户使用',
            'loginName.required' => '登录名不能为空',
            'loginName.unique' => '登录名已被商户使用',
            'password.required' => '密码不能为空',
            'service_fee.required' => '费率不能为空',
            'cust_capital_amount.min' => '商户初始金额不能小于0'
        ]);
        if($validate->fails()){
            return failReturn($validate->errors()->first());
        }
        $agent = Agent::query()->where('id',Auth::user()->agent_id)->first();
        if($request->input('service_fee') < ($agent->fee_rate)){
            return failReturn('商户费率不能低于自己');
        }
        $amount = $request->input('cust_capital_amount') * ($agent->fee_rate) / $request->input('service_fee');
        $amount = sprintf("%.3f", $amount);
        if($amount > $agent->account_left){
            return failReturn('您的资金不足以支付本次充值！');
        }
        DB::beginTransaction();
        try{
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $data['service_fee'] = $data['service_fee'] / 100;
            $data['merchant_id'] = makeMerchant();
            $data['agent_id'] = Auth::user()->agent_id;
            $left = $agent->account_left -$amount;
            $agent ->account_left = $left;
            $request->request->add([
                'useAmount' => $amount,
                'account_left' => $left
            ]);
            $agent -> save();
            Customer::create($data);
            DB::commit();
            return jsonReturn([],'新增商户成功');
        }catch (\Exception $exception){
            DB::rollback();
            return failReturn('未知错误发生！');
            \Log::info($exception);
        }
    }

    public function getOneCustomer(Request $request){
        $id = $request->input('id');
        $data = Customer::query()
            ->where('id',$id)
            ->with(['Agent' => function($query){
                $query->select('id','name','fee_rate','account_left');
            }])
            ->first()
            ->toArray();
        $data = array_dot($data);
        return jsonReturn($data);
    }

    public function updateInfo(Request $request){
        $id = $request->input('id');
        $Customer = Customer::query()
            ->where('id',$id)
            ->first();
        $Customer->name = $request->input('name');
        $Customer->userName = $request->input('userName');
        $Customer->cellphone = $request->input('cellphone');
        $Customer->is_login_forbidden = $request->input('is_login_forbidden');
        if($request->input('password') != ''){
            $Customer->password = Hash::make($request->input('password'));
        }
        $Customer->save();
        return jsonReturn([],"更新商户信息成功");
    }

    public function updateFee(Request $request){
        $id = $request->input('id');
        $Customer = Customer::query()
            ->where('id',$id)
            ->first();
        if(!$Customer){
            return failReturn('商户信息错误');
        }
        $oldFee = $Customer->getOriginal('service_fee');
        $newFee = $request->input('service_fee');
        $agent = Agent::query()->where('id',Auth::user()->agent_id)->first();
        $selfFee = $agent->fee_rate;
        if($newFee < ($agent->fee_rate)){
            return failReturn('商户费率不能低于自己');
        }
        // 费率降低 补扣
        $cust_capital_amount = $Customer->cust_capital_amount;
        if($newFee < $oldFee && $cust_capital_amount > 0){
            $amount = $cust_capital_amount * (($selfFee/$newFee)-($selfFee/$oldFee));
            $amount = sprintf("%.3f", $amount);
            \Log::info($amount);
            if($amount > $agent->account_left){
                return failReturn('您的资金不足以支付本次扣费！');
            }
            $left = $agent->account_left -$amount;
            $agent ->account_left = $left;
            $request->request->add([
                'useAmount' => $amount,
                'account_left' => $left
            ]);
            $agent -> save();
        }
        $Customer->service_fee = $newFee;
        $Customer->save();
        return jsonReturn([],'更新商户费率成功！');
    }

    public function recharge(Request $request){
        $id = $request->input('id');
        $Customer = Customer::query()
            ->where('id',$id)
            ->first();
        if(!$Customer){
            return failReturn('商户信息错误');
        }
        $oldFee = $Customer->getOriginal('service_fee') * 100;
        $agent = Agent::query()->where('id',Auth::user()->agent_id)->first();
        $selfFee = $agent->fee_rate;
        $recharge = $request->input('rechargeAmount');
        \Log::info($recharge);
        $amount = $recharge * ($selfFee/$oldFee);
        $amount = sprintf("%.3f", $amount);
        if($amount > $agent->account_left){
            return failReturn('您的资金不足以支付本次充值！');
        }
        $left = $agent->account_left -$amount;
        DB::beginTransaction();
        try{
            $agent ->account_left = $left;
            $request->request->add([
                'useAmount' => $amount,
                'account_left' => $left
            ]);
            $agent -> save();
            $Customer->cust_capital_amount =  $Customer->cust_capital_amount + $recharge;
            $Customer->save();
            DB::commit();
            return jsonReturn([],'商户充值成功');
        }catch (\Exception $exception){
            DB::rollback();
            return failReturn('未知错误发生！');
            \Log::info($exception);
        }
    }

    public function orderList(){
        $user = Auth::user();
        try{
            $list = CustomerRepository::getOrderList($user);
            return jsonReturn($list);
        }catch (\Exception $exception){
            return failReturn("未知错误发生！请稍后再试！");
        }
    }


    public function flowList(){
        $user = Auth::user();
        try{
            $list = CustomerRepository::getFlowList($user);
            return jsonReturn($list);
        }catch (\Exception $exception){
            dd($exception);
            return failReturn("未知错误发生！请稍后再试！");
        }
    }
}
