<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;
use App\Http\Model\User;

class UserRepository extends BaseRepository
{
    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = User::query();
        return BaseRepository::info($id, $params, $query);
    }

    public static function getAdminByAgentID($agent_id)
    {
        return self::getInfo(['agent_id' => $agent_id, 'role_id' => 1]);
    }
}