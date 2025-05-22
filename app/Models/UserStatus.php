<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserStatus extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_TERMINATED = 'terminated';

    public const STATUS_INACTIVE_ABANDONED = 'inactive-abandoned';

    public const STATUS_HIATUS = 'hiatus';

    public const STATUS_APPLICANT_ABANDONED = 'applicant-abandoned';

    public const STATUS_APPLICANT_DENIED = 'applicant-denied';

    public const STATUS_APPLICANT = 'applicant';

    public const STATUS_INACTIVE_IN_MEMORIAM = 'inactive-in-memoriam';

    public const STATUSES_TO_ACTIVE_SEND_INVITES = [
        self::STATUS_INACTIVE,
        self::STATUS_TERMINATED,
        self::STATUS_INACTIVE_ABANDONED,
        self::STATUS_APPLICANT_ABANDONED,
        self::STATUS_APPLICANT_DENIED,
        self::STATUS_APPLICANT,
    ];

    protected $fillable = ['user_id', 'status', 'updated_by', 'note', 'created_at', 'updated_at'];

    // returns user the request is for
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
