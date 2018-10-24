<?php

namespace App\Http\Controllers;

use App\Common;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const PAGE_SIZE = 15;
    const CAPTCHA_PREFIX = "captcha_";
    const CAPTCHA_CACHE = "redis";
    const CODE_SUCCESS = 1;
    const CODE_FAIL = 0;
    const NOTICE_CHG_PWD = 1;

    static function getUser(){
        return Common::getUser();
    }

    static function getUserAgent(){
        return Common::getUser();
    }

    /**
     * 获取验证码 重新获取验证码
     * @param $captchaId ,$captchaCode
     * @return bool
     */
    static function verifyCaptchaCode($captchaId, $captchaCode): bool
    {
        $cacheKey = self::CAPTCHA_PREFIX . $captchaId;
        $cachedCode = Cache::store(self::CAPTCHA_CACHE)->get($cacheKey);
        //Cache::forget($cacheKey);
        return $cachedCode == $captchaCode;
    }

    /**
     * 设置图片验证码
     * @param $captchaId
     * @return string 返回图片base64 string
     */
    static function generateCaptchaImage($captchaId): string
    {
        $phraseBuilder = new PhraseBuilder(5, '0123456789');
        $builder = new CaptchaBuilder(null, $phraseBuilder);
        $builder->setDistortion(false);
        $builder->setIgnoreAllEffects(true);
        $builder->build();
        $cacheKey = self::CAPTCHA_PREFIX . $captchaId;
        Cache::store(self::CAPTCHA_CACHE)->put($cacheKey, $builder->getPhrase(), 5);
        return $builder->inline();
    }
}
