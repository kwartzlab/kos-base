<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
 
class Role extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $table = 'roles';
    protected $fillable = ['name','description','permissions'];

    public function permissions() {
        return $this->hasMany(RolePermission::class, 'role_id', 'id');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_roles');
    }


}
