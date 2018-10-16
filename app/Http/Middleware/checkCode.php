<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\Controller;

class checkCode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->offsetSet('client_id', config('app.client_id'));
        $request->offsetSet('client_secret', config('app.client_secret'));
        if ($request->input('grant_type') == 'refresh_token') return $next($request);
        $captchaId = $request->input('captchaId');
        $captchaCode = $request->input('captchaCode');
        if(!$captchaId || !$captchaCode || !Controller::verifyCaptchaCode($captchaId,$captchaCode)) {
            return Controller::jsonReturn([], Controller::CODE_FAIL, '图像验证码错误');
        }
        return $next($request);
    }
}
