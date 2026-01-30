<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmissionOutbox;
use Illuminate\Http\Request;

class FormSubmissionOutboxController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));
        return FormSubmissionOutbox::query()
            ->select([
                'id',
                'form_submission_id',
                'processed_at',
                'last_error',
                'last_error_at',
                'created_at',
            ])
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function next()
    {
        $cutoff = now()->subMinutes(5);

        $outboxItem = FormSubmissionOutbox::whereNull('processed_at')
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_error_at')
                    ->orWhere('last_error_at', '<=', $cutoff);
            })
            ->orderBy('created_at')
            ->first();

        if ($outboxItem === null) {
            return response()->json(['message' => 'No pending form submissions.'], 204);
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
            'last_error' => $outboxItem->last_error,
            'last_error_at' => optional($outboxItem->last_error_at)->toDateTimeString(),
        ]);
    }

    public function markProcessed(Request $request, FormSubmissionOutbox $formSubmissionOutbox)
    {
        $lastError = $request->input('last_error');
        $updates = [];

        if (! $lastError) {
            $updates['processed_at'] = now();
        } else {
            $updates['processed_at'] = null;
            $updates['last_error'] = $lastError;
            $updates['last_error_at'] = now();
        }

        $formSubmissionOutbox->fill($updates)->save();

        return response()->json([
            'outbox_id' => $formSubmissionOutbox->id,
            'processed_at' => optional($formSubmissionOutbox->processed_at)->toDateTimeString(),
            'last_error' => $formSubmissionOutbox->last_error,
            'last_error_at' => optional($formSubmissionOutbox->last_error_at)->toDateTimeString(),
        ]);
    }

    public function show(FormSubmissionOutbox $formSubmissionOutbox)
    {
        $formSubmission = $formSubmissionOutbox->formSubmission;

        if ($formSubmission === null) {
            return response()->json(['message' => 'Form submission not found.'], 404);
        }

        return response()->json([
            'outbox_id' => $formSubmissionOutbox->id,
            'form_submission_id' => $formSubmission->id,
            'form_name' => $formSubmission->form_name,
            'special_form' => $formSubmission->special_form,
            'submitted_by' => $formSubmission->submitted_by,
            'user_id' => $formSubmission->user_id,
            'data' => json_decode($formSubmission->data, true),
            'created_at' => optional($formSubmission->created_at)->toDateTimeString(),
            'last_error' => $formSubmissionOutbox->last_error,
            'last_error_at' => optional($formSubmissionOutbox->last_error_at)->toDateTimeString(),
        ]);
    }
}
