<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';
    protected $fillable = ['object','operation','role_id'];

    public function role() {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
    
}
