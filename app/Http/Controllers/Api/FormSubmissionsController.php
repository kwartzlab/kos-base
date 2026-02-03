<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        return FormSubmission::query()
            ->select([
                'id',
                'form_id',
                'form_name',
                'user_id',
                'created_at',
            ])
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function show(Request $request, FormSubmission $formSubmission)
    {
        return response()->json([
            'form_id' => $formSubmission->form_id,
            'form_submission_id' => $formSubmission->id,
            'form_name' => $formSubmission->form_name,
            'user_id' => $formSubmission->user_id,
            'data' => json_decode($formSubmission->data, true),
        ]);
    }
}
