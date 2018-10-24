<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use App\Common;
use Illuminate\Database\Eloquent\Builder;

class BaseRepository
{
    static function getUser()
    {
        return Common::getUser();
    }

    static function getUserAgent()
    {
        return Common::getUser();
    }

    /**
     * Register the global scopes for this builder instance.
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public static function lists($params, $val = '', Builder $query)
    {
        if (is_array($params)) {
            if ($val) return $query->where($params)->get();
            $querys = $query->querys($params);
            if (empty($params['count'])) {
                return $querys->get();
            }
            $rs['list'] = $querys->get();
            $rs['count'] = $querys->count;
            return $rs;
        } else {
            if (is_array($val)) {
                return $query->whereIn($params, $val)->get();
            }
            return $query->where($params, $val)->get();
        }
    }

    public static function info($id, $params, Builder $query)
    {
        if(!$id) return false;
        if(is_array($id)) return $query->where($id)->first();
        if(is_numeric($id)) return $query->querys($params)->find($id);
        return $query->where($id, $params)->first();
    }

    public static function validates($params, $fieldAble)
    {
        $rules = [];
        $messages = [];
        return static::makeValidator($params, $fieldAble, $rules, $messages);
    }

    public static function makeValidator($params, $fieldAble, $rules, $messages, $strict = true)
    {
        $arr = [];
        foreach ($fieldAble as $v) {
            if ($strict) {
                if (isset($rules[$v])) {
                    $arr[$v] = $rules[$v];
                }
            } else {
                if (isset($params[$v]) && isset($rules[$v])) {
                    $arr[$v] = $rules[$v];
                }
            }
        }
        $validator = \Validator::make($params, $arr, $messages);
        return $validator;
    }

}