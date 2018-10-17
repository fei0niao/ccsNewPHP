<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;

class Agent extends Base
{
    protected $table = "agent";

    public function getSelfRelationAttribute(){
        return $this->relation . $this->id . '%';
    }
}
