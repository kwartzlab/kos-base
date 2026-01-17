<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmissionOutbox;
use Illuminate\Http\Request;

class ApiFormSubmissionOutboxController extends Controller
{
    public function next()
    {
        $outboxItem = FormSubmissionOutbox::whereNull('processed_at')
            ->orderBy('created_at')
            ->first();

        if ($outboxItem === null) {
            return response()->json(['message' => 'No pending form submissions.'], 404);
        }

        $formSubmission = $outboxItem->formSubmission;

        if ($formSubmission === null) {
            return response()->json(['message' => 'Form submission not found.'], 404);
        }

        return response()->json([
            'outbox_id' => $outboxItem->id,
            'form_submission_id' => $formSubmission->id,
            'form_name' => $formSubmission->form_name,
            'special_form' => $formSubmission->special_form,
            'submitted_by' => $formSubmission->submitted_by,
            'user_id' => $formSubmission->user_id,
            'data' => json_decode($formSubmission->data, true),
            'created_at' => optional($formSubmission->created_at)->toDateTimeString(),
        ]);
    }

    public function markProcessed(Request $request, FormSubmissionOutbox $formSubmissionOutbox)
    {
        if ($formSubmissionOutbox->processed_at !== null) {
            return response()->json(['message' => 'Outbox item already processed.'], 409);
        }

        $lastError = $request->input('last_error');
        $updates = ['last_error' => $lastError];

        if (!$lastError) {
            $updates['processed_at'] = now();
        }

        $formSubmissionOutbox->forceFill($updates)->save();

        return response()->json([
            'outbox_id' => $formSubmissionOutbox->id,
            'processed_at' => optional($formSubmissionOutbox->processed_at)->toDateTimeString(),
            'last_error' => $formSubmissionOutbox->last_error,
        ]);
    }
}
