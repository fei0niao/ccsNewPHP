<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use App\Http\Model\SystemSetting;

class SystemSettingRepository extends BaseRepository
{
    public static function getList($params, $val = '', $query = '')
    {
        if (!$query) $query = SystemSetting::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = SystemSetting::query();
        return BaseRepository::info($id, $params, $query);
    }

    public static function getParamValue($param_key = '', $agent_id = '')
    {
        $agent = static::getUserAgent();
        $paramList = \Cache::tags(__METHOD__)->remember(implode('-', func_get_args()), null, function () use ($agent) {
            return SystemSetting::where('agent_id', $agent->id)->get()->mapWithKeys(function ($item) {
                return [$item['param_key'] => $item['param_value']];
            });
        });
        if (!$param_key) return $paramList;
        if (is_string($param_key)) return $paramList[$param_key]??'';
        return filterArray($paramList, $param_key)??[];
    }
}