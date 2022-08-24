<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FormSubmission extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_id', 'form_name', 'submitted_by', 'user_id', 'data', 'submitter_ip', 'submitter_agent', 'special_form',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function submitter()
    {
        return $this->hasOne(User::class, 'id', 'submitted_by');
    }

    // returns true or false whether user can view form submission
    public function canview()
    {
        // only submitter, associated user and form managers can view associated forms for now
        if ((\Auth::user()->id == $this->submitted_by) || (\Auth::user()->id == $this->user_id) || (\Gate::allows('manage-forms'))) {
            return true;
        } else {
            // any user can view an applicant form
            if ($this->user()->get()->status == 'applicant') {
                return true;
            }

            return false;
        }
    }
}
