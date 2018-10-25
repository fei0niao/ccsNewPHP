<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Agent extends Base
{
    protected $table = "agent";

    public static $append_fields = [
    ];

    //权限控制 调用方式 模型名::permission()
    public function scopePermission($query)
    {
        $user = static::getUser();
        $agent = static::getUserAgent();
        if (!$agent_id = $user->agent_id) return $query;
        return $query->where(function ($query) use ($agent) {
            return $query->where('relation', 'like', $agent->relation . $agent->id . '_%')->orwhere('id', $agent->id);
        });
    }

    public function getStatusDefAttribute($val)
    {
        switch ($this->attributes['status']) {
            case 0:
                return '禁用';
            case 1:
                return '正常';
            case 2:
                return '欠费';
            default:
                return '未知';
        }
    }

    public function getSelfRelationAttribute(){
        return $this->relation . $this->id . '%';
    }

    public function getAccountLeftAttribute($value){
        return sprintf("%.2f", $value);
    }

    public function getLevelDefAttribute($val)
    {
        switch ($this->attributes['level']) {
            case 1:
                return 'K';
            case 2:
                return 'H';
            case 3:
                return 'T';
            case 4:
                return 'E';
            case 5:
                return 'Q';
            default:
                return '未知';
        }
    }

    public function agent(){
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function user(){
        return $this->hasOne(AdminUser::class, 'agent_id', 'id');
    }
}