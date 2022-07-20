<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Form extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'description', 'status', 'special_form', 'fields', 'conditions', 'actions'];

    public function submissions()
    {
        return $this->hasMany(FormSubmissions::class);
    }

    //
}
