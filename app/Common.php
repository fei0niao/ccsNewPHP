<?php

namespace App;

use Illuminate\Support\Facades\Auth;

class Common
{
    protected static $user = '';
    protected static $userAgent = '';

    static function getUser(){
        if(!static::$user) static::$user = Auth::user();
        return static::$user;
    }

    static function getUserAgent(){
        if(!static::$userAgent) static::$userAgent = static::getUser()->agent;
        return static::$userAgent;
    }
}