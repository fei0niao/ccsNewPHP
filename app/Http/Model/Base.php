<?php

namespace App\Http\Model;

use App\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations;
use Closure;

/**
 * App\Http\Model\Base
 *
 * @mixin \Eloquent
 */
class Base extends Model
{
    public static $modelName = '';
    protected $guarded = [];//没有此属性不能批量赋值

    protected $roundFields = []; //数据保留3位有效小数

    public static $_appends = [];
    public static $_hiddens = [];
    public static $_visibles = [];
    public static $_selects = [];//需要提交sql查询的字段
    public static $append_fields = [];//附加字段配置 不属于sql字段
    public static $_fields = [];//特殊情况:既属于sql字段 又依赖其它字段
    const BASE_PARAMS = ['field', 'where', 'orWhere', 'search', 'whereIn', 'whereBetween', 'whereNull', 'has', 'count', 'order', 'offset', 'limit', 'keyword', 'extra'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $modelName = get_class($this->getModel());

        //用self会有问题 不会用子类的$ConfigAppends属性
        if (!empty(static::$_appends[$modelName])) $this->appends = array_merge($this->appends, static::$_appends[$modelName]);
        if (!empty(static::$_hiddens[$modelName])) $this->hidden = array_merge($this->hidden, static::$_hiddens[$modelName]);
        if (!empty(static::$_visibles[$modelName])) $this->visible = array_merge($this->visible, static::$_visibles[$modelName]);
    }

    static function getUser(){
        return Common::getUser();
    }

    static function getUserAgent(){
        return Common::getUserAgent();
    }

    public static function assignAppends($val)
    {
        $val = is_array($val) ? $val : func_get_args();
        return static::$_appends[static::$modelName] = array_merge(static::$_appends[static::$modelName]??[], $val);
    }

    public static function assignHiddens($val)
    {
        $val = is_array($val) ? $val : func_get_args();
        return static::$_hiddens[static::$modelName] = array_merge(static::$_hiddens[static::$modelName]??[], $val);
    }

    public static function assignVisibles($val)
    {
        $val = is_array($val) ? $val : func_get_args();
        return static::$_visibles[static::$modelName] = array_merge(static::$_visibles[static::$modelName]??[], $val);
    }

    public static function assignSelects($val)
    {
        $val = is_array($val) ? $val : func_get_args();
        //指定key不然会冲突
        return static::$_selects[static::$modelName] = array_unique(array_merge(static::$_selects[static::$modelName]??[], $val));
    }

    //query()链式调用 改进select方法 可以任意选择显示需要的字段（数据库字段+模型中附加字段）
    public function scopeSelects($query, $val)
    {
        //很关键 必须找到调用的model 否则static::$append_fields始终指向第一次调用的model
        $model = $query->getModel();
        static::$modelName = get_class($model);
        static::$append_fields = $model::$append_fields;
        static::$_fields = $model::$_fields;
        $noNeedSelect = in_array('*', $val);
        static::$_appends = static::$_hiddens = static::$_visibles = [];//初始化 否则自己关联自己（父子)的场景会报错
        if (!$noNeedSelect && !static::$append_fields && !static::$_fields) return $query->select($val);
        //参数变成数组
        if (!is_array($val)) {
            $val = func_get_args();
            array_shift($val);//第一个参数为$query去掉
        }
        if ($noNeedSelect) {
            foreach ($val as $k => $v) {
                if ($v == '*' || strpos($v, ' as ')) continue;
                static::assignAppends($v);
                unset($val[$k]);
            }
            return $query->select(array_values($val));
        }
        foreach ($val as $v) {
            //若字段判断为附加字段 则放到appends属性里
            if (isset(static::$append_fields[$v])) {
                $append_fields = (array)static::$append_fields[$v];
                $diff = array_diff($append_fields, $val);
                if ($diff) static::assignHiddens($diff);
                static::assignAppends($v);
                //将appends所有字段 加入sql查询字段
                if ($append_fields) static::assignSelects($append_fields);
            } else if (isset(static::$_fields[$v])) {
                //特殊情况 key和value都加入sql查询字段
                static::assignSelects($v, ...(array)static::$_fields[$v]);
            } else {
                static::assignSelects($v);
            }
        }
        return $query->select(static::$_selects[static::$modelName]);
    }


    //权限控制 调用方式 模型名::permission()
    public function scopePermission($query)
    {
        return $query;
        /*$id = (static::$user)->id;
        $role_id = (static::$user)->role_id;
        $agent_id = (static::$user)->agent_id;
        if (in_array($role_id, [1, 2, 3, 4, 5, 6, 7, 8, 9])) {
            return $query;
        } elseif ($role_id == 16) {
            $cust_ids = ClientAgentEmployeeRelation::getAllChildrenCustsByEmpID($id);
            return $query->where('agent_id', $agent_id)->whereIn('cust_id', $cust_ids);
        } elseif (in_array($role_id, [11, 12, 13, 14, 15])) {
            $agent_ids = Agent::getAllChildrenAgentsByAgentID($agent_id);
            return $query->whereIn("agent_id", $agent_ids);
        }
        return abort(403, '未授权！');*/
    }

    //更新数据的权限控制
    public function scopeUpdatePermission($query)
    {
        return $query;
    }

    //创建数据的权限控制
    public function scopeCreatePermission($query)
    {
        return $query;
    }

    //过滤参数 若有部分自定义参数 则执行指定代码
    public function filterParams($query, array $params)
    {
        return $params;
    }

    //传入参数 params自动解析:  json配置->直接生成sql语句
    public function scopeQuerys($query, array $params)
    {
        //过滤参数 若有部分自定义参数 则执行指定代码
        $params = $this->filterParams($query, $params);
        //两层过滤 第一层为数量过滤 第二层为字段、排序等其他过滤
        $this->scopeFilter1($query, $params);
        //添加count属性
        $query->count = empty($params['count']) ? '' : $query->count();
        $this->scopeFilter2($query, $params);
        //dump($params);
        //过滤掉params中的常用过滤参数
        $params = array_filter($params, function ($key) {
            return !in_array($key, self::BASE_PARAMS);
        }, ARRAY_FILTER_USE_KEY);
        if (!$params) return $query;
        //循环分析参数
        foreach ($params as $k_model_name => $v_params) {
            //若有count 直接withCount增加 model_count字段
            if (isset($v_params['count']) && $v_params['count']) {
                $query->withCount([$k_model_name => function ($k_query) use ($v_params) {
                    $this->scopeFilter1($k_query, $v_params);
                }]);
                unset($v_params['count']);
            }
            //若排除掉count字段 还有其它字段 则with加载
            $query->with([$k_model_name => function ($k_query) use ($v_params, $k_model_name) {
                if (!$v_params) return $k_query;
                //自动将主键加入with中的field查询
                if (!empty($v_params['field']) && !in_array('*', $v_params['field'])) {
                    if ($k_query instanceof Relations\BelongsTo) {
                        $v_params['field'][] = $k_query->getOwnerKey();
                    } elseif ($k_query instanceof Relations\HasOne) {
                        $v_params['field'][] = $k_query->getForeignKeyName();
                    }
                }
                //引用自己 递归执行
                $this->scopeQuerys($k_query, $v_params);
            }]);
        }
        //返回with加载结果 同时终止递归
        return $query;
    }

    //model 第一层数量过滤 withCount使用正好
    public function scopeFilter1($query, $params)
    {
        //where过滤
        if (!empty($params['where'])) {
            foreach ($params['where'] as $k => $v) {
                if ($v === null) continue;
                if (is_array($v) && count($v) == 2) {
                    $where[] = [$k, $v[0], $v[1]];
                } else {
                    $where[] = [$k, $v];
                }
            }
            if (!empty($where)) $query->where($where);
        }
        if (!empty($params['orWhere'])) {
            $orWhere = $params['orWhere'];
            $query->where(function ($orWhereQuery) use ($orWhere) {
                foreach ($orWhere as $k => $v) {
                    if ($v === null) continue;
                    if (is_array($v) && count($v) == 2) {
                        $orWhereQuery->orWhere($k, $v[0], $v[1]);
                    } else {
                        $orWhereQuery->orWhere($k, $v);
                    }
                }
            });
        }
        //whereBetween过滤 目前仅用于时间
        if (!empty($params['whereBetween'])) {
            foreach ($params['whereBetween'] as $k => $v) {
                if ($v === null) continue;
                if (!empty($v[0]) && !empty($v[1])) $query->whereBetween($k, $v);
                else if (!empty($v[0])) $query->where($k, '>=', $v[0]);
                else if (!empty($v[1])) $query->where($k, '<=', $v[1]);
            }
        }
        //where like过滤
        if (!empty($params['search'])) {
            foreach ($params['search'] as $k => $v) {
                if ($v === null) continue;
                $search[] = [$k, 'like', '%' . $v . '%'];
            }
            if (!empty($search)) $query->where($search);
        }
        //whereIn过滤
        if (!empty($params['whereIn'])) {
            foreach ($params['whereIn'] as $k => $v) {
                if ($v === []) {
                    return false;//判断为不通过
                } else if (empty($v)) {
                    continue;
                } else {
                    $query->whereIn($k, $v);
                }
            }
        }
        //whereNull过滤
        if (!empty($params['whereNull'])) $query->whereNull($params['whereNull']);
        //has过滤 whereHasIn用得in语法 whereHas用的exists语法
        if (!empty($params['has'])) {
            foreach ($params['has'] as $key => $has) {
                if (!$has) $query->whereHasIn($key);
                else {
                    if (!empty($has['where'])) {
                        foreach ($has['where'] as $k => $v) {
                            if (!$v) continue;
                            $query->whereHasIn($key, function ($query) use ($k, $v) {
                                $query->where($k, $v);
                            });
                        }
                    }
                    if (!empty($has['search'])) {
                        foreach ($has['search'] as $k => $v) {
                            if (!$v) continue;
                            $query->whereHasIn($key, function ($query) use ($k, $v) {
                                $query->where($k, 'like', '%' . $v . '%');
                            });
                        }
                    }
                }
            }
        }
        return $query;
    }

    //model 第二层为字段、排序等其他过滤
    public function scopeFilter2($query, $params)
    {
        if (empty($params['field'])) $params['field'] = ['*'];
        $this->scopeSelects($query, $params['field']);
        //排序并限制数量
        if (!empty($params['order'])) $query->orderByRaw($params['order']);
        if (!empty($params['offset'])) $query->offset($params['offset']);
        //若想设置无限制 则limit需设置为0
        if (!empty($params['limit'])) $query->limit($params['limit']);
        return $query;
    }

    public function scopeWhereHasIn($query, $relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        /*if (strpos($relation, '.') !== false) {
            return $this->hasNested($relation, $operator, $count, 'and', $callback);
        }*/
        $relation = Relations\Relation::noConstraints(function () use ($relation) {
            return $this->getModel()->{$relation}();
        });
        //子查询
        $query_sub = $relation->getRelationExistenceQuery(
            $relation->getRelated()->newQuery(), $query, $relation->getOwnerKey()
        );
        if ($callback) $callback($query_sub);
        if ($relation instanceof Relations\BelongsTo) {
            return $query->whereIn($relation->getForeignKey(), $query_sub->mergeConstraintsFrom($relation->getQuery()));
        } elseif ($relation instanceof Relations\HasOne) {
            return $query->whereIn($this->getKeyName(), $query_sub->mergeConstraintsFrom($relation->getQuery()));
        }
        return $query;
    }

    //增加新的计算属性
    public function getMutatedAttributes()
    {
        $attributes = parent::getMutatedAttributes();
        return array_merge($attributes, $this->roundFields);
    }

    //定义新的计算属性处理方式
    protected function mutateAttributeForArray($key, $value)
    {
        if (in_array($key, $this->roundFields)) {
            return round($value, 3);
        }
        return parent::mutateAttributeForArray($key, $value);
    }

    //boot方法每次调用不同的类都会去执行 访问相同的类不会执行
    public static function boot()
    {
        parent::boot();
    }
}
