<?php
use Lcobucci\JWT\Parser;
use Illuminate\Support\Facades\DB;

function jsonReturn($data = [], string $message = '', int $code_status = 1)
{
    $json['status'] = $code_status ? 1 : 0;
    $json['data'] = $data;
    $json['msg'] = $message;
    if (config('app.debug')) {
        $json['debug_sql'] = \DB::getQueryLog();
    }

    //这里没返回一个response对象而直接结束，因为可能在除控制器外的其他地方会调用
    $content = response()->json($json)->getContent();
    echo $content;
    exit;
}

function failReturn(string $message = '')
{
    $json['status'] = 0;
    $json['data'] = [];
    $json['msg'] = $message;
    if (config('app.debug')) {
        $json['debug_sql'] = \DB::getQueryLog();
    }
    //这里没返回一个response对象而直接结束，因为可能在除控制器外的其他地方会调用
    $content = response()->json($json)->getContent();
    echo $content;
    exit;
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
