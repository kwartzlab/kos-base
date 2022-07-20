<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Trainers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'gatekeeper_id',
    ];

    // returns the name of the trainer this record is for
    public function name()
    {
        $result = \App\User::where('id', $this->user_id)->get();

        foreach ($result->pluck('first_name', 'last_name') as $lastname => $firstname) {
            $full_name = $firstname.' '.$lastname;
        }

        return $full_name;
    }

    // returns the name of the gatekeeper the training record is for
    public function gatekeeper()
    {
        $result = \App\Gatekeeper::where('id', $this->gatekeeper_id)->get();

        return $result->pluck('name');
    }
}
