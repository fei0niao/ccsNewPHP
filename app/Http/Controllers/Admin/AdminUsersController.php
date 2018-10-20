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
            dd($exception);
            return failReturn("未知错误发生！请稍后再试！");
        }
    }

    function customerLogin($id){
        $data = [
            "id" => $id,
            "time" => date('YmdHis'),
            "type" => 2
        ];
        $sign = json_encode($data);
        $priKey="-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC+ArElfja8bdm1+Mngv34tluxMYUR3sLhmkWbV8513J6rQ05Ok、
vgYnRaxQ5L2lvUVkXC1KJs+z77uCK1ymUaSN9B4ektonotRwx4qzETsxRBib6gP8
ThpS+AtWLePV3hZ7+1tpHOpCDroMdS8Q65qwRrsRe+J4SNqZGwjUhAVf+wIDAQAB
AoGAWIbcjgFd8zCjDHtbY1EUspzsfzGaOsGlSHRaGzijls5ucVkCIvE94LI/dHj+
OugSGo4vs6qdftIk5KLbScokBgiwrV3hD4z3QbetuOCfC+o7qA2bK9Op24GMI/DH
Hz/TBumwkKmephXPA7+D9/P6woEb2BaleZ3OOMJGPFRmnOECQQD0KUr9lZvOA5+l
aXOcmExHPZh8fD2fYIAFuKZUpdV+1Eq9RGKk2XfwRUwWjj/TdNMokwGO4cWpHCpU
Q/ox9mhpAkEAxzk9gMpLSlIG4mHEaGEgCNku6fk4T97wYTbJ9ztI50NX2oqjld5R
2okg5lGbuLsdBcF/drTv1Xy/Od/PnfoYwwJBAMDGWY8eMIXYFpRjTgS1uoQE/gBL
l9veNTZPNARhas9YjiohdEDz8t6h2BF2/q3V72J5ryFA4O9EbadahJAuHQECQQC4
1HAtBoFniEZ+zPmtZT6VNvmBdQg7gbg+WNhzmPsAI8hkJu+x4TrLpyFwzRHOBzrb
1jNtbFx+EmhPR0eVZyyFAkAGHagyQh0dGVAHetMs3lGLvOjgOn5sFe00c2BFNWyA
TYO6yiMWh2k4vJ9bLEUNPeQbRemEJ/q1p5uvP0FfeVt1
-----END RSA PRIVATE KEY-----";
        $token = private_encrypt($priKey, $sign);
        $url = env('WebUrl')."/AdminLogin?access_token=".$token;
        header("location:".$url);
        exit();
    }
}
