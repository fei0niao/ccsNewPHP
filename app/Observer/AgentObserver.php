<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/18 0018
 * Time: 16:32
 */

namespace App\Observer;


use App\Http\Model\ActionLog;
use App\Http\Model\Agent;
use Illuminate\Support\Facades\Auth;

class AgentObserver
{
    public function saved(Agent $customer){
        $dirty = $customer->getDirty();
        $origin = $customer->getOriginal();
        try{
            $data = [
                'ip' => request()->ip(),
                'user_id' => Auth::user()->id,
                'oldData' => json_encode($origin),
                'newData' => json_encode($dirty),
                'remark'  => $origin ? "代理商数据更新！" : "新建代理商"
            ];
            \Log::info($data);
            ActionLog::create($data);
        }catch (\Exception $exception){
            \Log::info($exception);
        }

    }
}
