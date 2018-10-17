<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17 0017
 * Time: 15:15
 */

namespace App\Http\Controllers\Admin;


use App\Http\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Auth;

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
}
