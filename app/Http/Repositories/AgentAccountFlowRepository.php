<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16 0016
 * Time: 18:31
 */

namespace App\Http\Repositories;

use App\Http\Model\AgentAccountFlow;

class AgentAccountFlowRepository extends BaseRepository
{
    public static function getList($params = [], $val = '', $query = '')
    {
        if (!$query) $query = AgentAccountFlow::query();
        return BaseRepository::lists($params, $val, $query);
    }

    public static function getInfo($id, $params = '', $query = '')
    {
        if (!$query) $query = AgentAccountFlow::query();
        return BaseRepository::info($id, $params, $query);
    }

    public static function create($data, $returnModel = false)
    {
        $fieldAble = ['agent_id', 'flow_type', 'amount_of_account', 'account_left', 'order_number', 'remark','fee_rate'];
        $params = filterArray($data, $fieldAble);
        $validator = AgentAccountFlowRepository::validates($params, $fieldAble);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $rs = AgentAccountFlow::createPermission()->create($params);
        if ($returnModel) return jsonReturn($rs);
        return jsonReturn([], '创建成功！');
    }
}