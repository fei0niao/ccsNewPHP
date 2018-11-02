<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17 0017
 * Time: 11:34
 */

namespace App\Observer;

use App\Http\Model\ActionLog;
use App\Http\Model\AdminUser;
use App\Http\Model\User;
use Illuminate\Support\Facades\Auth;

class AdminUserObserver
{
    public function saved(AdminUser $user){
        // todo 记录变更
        $dirty = $user->getDirty();
        $origin  = $user->getOriginal();
        $data = [
            'ip' => request()->ip(),
            'user_id' => Auth::user()->id,
            'oldData' => json_encode($origin),
            'newData' => json_encode($dirty),
            'remark'  => $origin ? "(用户id：". $origin['id'] .")后台用户更新！" : "新增后台用户(用户id".$dirty['id'].")"
        ];
        ActionLog::create($data);
    }
}
