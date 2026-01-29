<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmissionOutbox extends Model
{
    protected $table = 'form_submission_outbox';

    // We manually set timestamps for this
    public $timestamps = false;

    protected $fillable = [
        'form_submission_id',
        'processed_at',
        'last_error',
        'last_error_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    /**
     * 1:1 relationship back to the form submission
     */
    public function formSubmission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }
}
