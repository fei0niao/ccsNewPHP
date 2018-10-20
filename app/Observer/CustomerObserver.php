<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18 0018
 * Time: 16:22
 */

namespace App\Observer;


use App\Http\Model\ActionLog;
use App\Http\Model\AgentAccountFlow;
use App\Http\Model\CustAccountFlow;
use App\Http\Model\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    public function saved(Customer $customer){
        $dirty = $customer->getDirty();
        $origin = $customer->getOriginal();
        try{
            $id = Auth::user()->id;
            $data = [
                'ip' => request()->ip(),
                'user_id' => $id,
                'oldData' => json_encode($origin),
                'newData' => json_encode($dirty),
                'remark'  => $origin ? $customer->id . "  商户信息更新" : "新增商户！" . $customer->id
            ];
            ActionLog::create($data);
            // 商户金额变更
            if(array_key_exists('cust_capital_amount',$dirty) && $dirty['cust_capital_amount']){
                if(array_key_exists('cust_capital_amount',$origin)){
                    $amount_of_account = $dirty['cust_capital_amount'] - $origin['cust_capital_amount'];
                }else{
                    $amount_of_account = $dirty['cust_capital_amount'];
                }
                $data = [
                    'cust_id' => $dirty['id'] ?? $origin['id'],
                    'flow_type' => 1,
                    'account_left' => $dirty['cust_capital_amount'],
                    'amount_of_account' => $amount_of_account,
                    'remark'  => $origin ? '商户充值' :  '新增商户初始金额'
                ];
                // 商户余额被更改生成商户流水
                CustAccountFlow::create($data);
                // 代理商流水记录
                $flowData = [
                    'cust_id' => $id,
                    'flow_type' => 2,
                    'amount_of_account' => request()->input('useAmount'),
                    'account_left'    => request()->input('account_left'),
                    "remark"  => $origin ? '对商户id为'.$customer->id.'的商户充值' : '商户id为'.$customer->id.'创建时初始金额（'.$dirty['cust_capital_amount'].'）扣款'
                ];
                AgentAccountFlow::create($flowData);
            }
            // 商户费率变更影响自己代理金额
            if(array_key_exists('service_fee',$dirty) && $origin && request()->has('useAmount')){
                $flowData = [
                    'cust_id' => $id,
                    'flow_type' => 2,
                    'amount_of_account' => request()->input('useAmount'),
                    'account_left'    => request()->input('account_left'),
                    "remark"  =>  '对商户id为'.$customer->id.'的商户费率变更追加扣款'
                ];
                AgentAccountFlow::create($flowData);
            }
        }catch (\Exception $exception){
            \Log::info($exception);
        }
    }
}
