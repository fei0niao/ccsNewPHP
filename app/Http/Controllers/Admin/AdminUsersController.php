<?php

namespace App\Http\Controllers\Admin;

use App\Http\Repositories\AdminUserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\AdminUser;
use Illuminate\Support\Facades\Auth;

class AdminUsersController extends Controller
{

    private $adminUserRepository = null;
    public function __construct(AdminUserRepository $adminUserRepository)
    {
        $this->adminUserRepository = $adminUserRepository;
    }

    public function userInfo(Request $request)
    {
        $user = Auth::user();
        try{
            $data = $this->adminUserRepository->getInfo($user);
            return jsonReturn($data);
        }catch (\Exception $exception){
            dd($exception->getMessage());
            return failReturn("未知错误发生！请稍后再试！");
        }
    }
}
