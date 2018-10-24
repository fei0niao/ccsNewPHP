<?php
use Lcobucci\JWT\Parser;
use Illuminate\Support\Facades\DB;
use App\Http\Model\User;
use Illuminate\Http\Request;

function jsonReturn($data = [], string $message = '', int $code_status = 1)
{
    $json['status'] = $code_status ? 1 : 0;
    $json['data'] = $data;
    $json['msg'] = $message;
    if (config('app.debug')) {
        $json['debug_sql'] = \DB::getQueryLog();
    }
    return $json;
    //return response()->json($json);
}

function failReturn(string $message = '')
{
    $json['status'] = 0;
    $json['data'] = [];
    $json['msg'] = $message;
    if (config('app.debug')) {
        $json['debug_sql'] = \DB::getQueryLog();
    }
    return $json;
    //return response()->json($json);
}

function dispatchRoute($route, $data, $returnModel = false, $method = 'POST')
{
    $request = request();
    if ($returnModel) $data += ['returnModel' => true];
    $request->request->replace($data);
    $proxy = Request::create(
        $route,
        $method
    );
    $rs = \Route::dispatch($proxy);
    $ret = json_decode($rs->getContent(), true);
    if (!$ret) {
        echo $rs;
        exit;
    }
    return $ret;
}

function round2($value)
{
    return floor($value * 100) / 100;
}

function filterArray($params, $fieldAble)
{
    $arr = [];
    foreach ($params as $key => $v) {
        if (array_search($key, $fieldAble)!==false) {
            $arr[$key] = $v;
        }
    }
    return $arr;
}

if (!function_exists("parsePassportAuthorization")) {
    function parsePassportAuthorization($request)
    {
        $authorization = $request->header("Authorization");
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $authorization));
        try {
            $token = (new Parser())->parse($jwt);
            $data = [
                "sub" => $token->getClaim("sub"),   //用户id
                "jti" => $token->getClaim("jti"),   //加密token值
                //要其他数据自己取
            ];
        } catch (\Exception $e) {
            return false;
        }

        return $data;
    }
}

if (!function_exists("getAgent")) {
    function getAgent(User $user)
    {
        if ($user->agent_id) {
            $agent = \App\Http\Model\Agent::query()
                ->where("id", $user->agent_id)
                ->select('name', 'level', 'fee_rate', 'account_left')
                ->first()
                ->toArray();
            return $agent;
        } else {
            return [];
        }
    }
}
if (!function_exists("makeMerchant")) {
    function makeMerchant()
    {
        $str = "";
        $code = "abcdefghijkmnopqrstuvwxyz1234567890";
        for ($i = 0; $i < 16; $i++) {
            $str .= $code[mt_rand(0, strlen($code) - 1)];
        }
        return $str;
    }
}

/*
 * 负载均衡用户真实ip
 * @return ip
 * */
if (!function_exists("getRealIp")) {
    function getRealIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipArr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $ipArr[0];
            return $ip;
        }
        if (isset($_SERVER['HTTP_WL_PROXY_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_WL_PROXY_CLIENT_IP'];
            return $ip;
        }
        $ip = request()->ip();
        return $ip == '::1' ? '127.0.0.1' : $ip;
    }
}


//私钥加密
if (!function_exists('private_encrypt')) {
    function private_encrypt($privateKey, $data)
    {
        $pri_key = openssl_get_privatekey($privateKey);
        if (!$pri_key) {
            return false;
        }
        openssl_private_encrypt($data, $priEncrypt, $pri_key);
        return base64_encode($priEncrypt);
    }
}

//私钥解密
if (!function_exists('private_decrypt')) {
    function private_decrypt($privateKey, $data)
    {
        $pri_key = openssl_get_privatekey($privateKey);
        if (!$pri_key) {
            return false;
        }
        openssl_private_decrypt(base64_decode($data), $decryptStr, $pri_key);
        return $decryptStr;
    }
}
