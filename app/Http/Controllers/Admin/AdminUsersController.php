<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Model\AdminUser;

class LoginController extends Controller
{
    public function info(Request $request)
    {
        return jsonReturn(AdminUser::first());
    }
}
