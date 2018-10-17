<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17 0017
 * Time: 11:34
 */

namespace App\Observer;

use App\Http\Model\User;
class UserObserver
{
    public function saved(User $user){
        // todo 记录变更
        $dirty = $user->getDirty();
        \Log::info($dirty);
        \Log::info(request()->user());
    }
}
