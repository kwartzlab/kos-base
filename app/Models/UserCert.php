<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserCert extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public const CERT_FIRST_AID = 'firstaid';
    public const CERT_PROFESSIONAL = 'professional';
    public const CERT_HEALTH_AND_SAFETY = 'healthsafety';
    public const CERT_OTHER = 'other';

    protected $fillable = ['user_id', 'type', 'name', 'expiry_date'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
