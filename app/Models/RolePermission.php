<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RolePermission extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'role_permissions';

    protected $fillable = ['object', 'operation', 'role_id'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
