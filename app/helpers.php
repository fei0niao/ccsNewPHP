<?php
function jsonReturn($data = [], string $message = '', int $code_status = 1, $isDebug = false)
{
    $json['status'] = $code_status ? 1 : 0;
    $json['data'] = $data;
    $json['msg'] = $message;
    if ($isDebug) {
        $json['debug_sql'] = \DB::getQueryLog();
    }

    //这里没返回一个response对象而直接结束，因为可能在除控制器外的其他地方会调用
    $content = response()->json($json)->getContent();
    echo $content;
    exit;
}

function failReturn(string $message = '', $isDebug = false)
{
    $json['status'] = 0;
    $json['data'] = [];
    $json['msg'] = $message;
    if ($isDebug) {
        $json['debug_sql'] = \DB::getQueryLog();
    }
    //这里没返回一个response对象而直接结束，因为可能在除控制器外的其他地方会调用
    $content = response()->json($json)->getContent();
    echo $content;
    exit;
}

