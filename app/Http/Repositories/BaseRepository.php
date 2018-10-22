<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Builder;

class BaseRepository
{
    /**
     * Register the global scopes for this builder instance.
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public static function lists($params, $val = '', Builder $query)
    {
        if (!$val) {
            $querys = $query->querys($params);
            if (empty($params['count'])) {
                return $querys->get();
            }
            $rs['list'] = $querys->get();
            $rs['count'] = $querys->count;
            return $rs;
        } else if (is_array($val)) return $query->whereIn($params, $val)->get();
        else return $query->where($params, $val)->get();
    }

    public static function info($id, $params, $val = '', Builder $query)
    {
        if ($id) $query->where('id', $id);
        if (!$val) return $query->querys($params)->first();
        else if (is_array($val)) return $query->whereIn($params, $val)->first();
        else return $query->where($params, $val)->first();
    }

    public static function validates($params, $fieldAble)
    {
        $rules = [];
        $messages = [];
        return static::makeValidator($params, $fieldAble, $rules, $messages);
    }

    public static function makeValidator($params, $fieldAble, $rules, $messages)
    {
        $arr = [];
        foreach ($fieldAble as $v) {
            if (isset($rules[$v])) {
                $arr[$v] = $rules[$v];
            }
        }
        $validator = \Validator::make($params, $arr, $messages);
        return $validator;
    }
}