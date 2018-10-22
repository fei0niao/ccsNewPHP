<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Agent extends Base
{
    protected $table = "agent";

    public static $append_fields = [
    ];

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
        return sprintf("%.3f", $value);
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
        return $this->hasOne(User::class, 'agent_id', 'id');
    }
}