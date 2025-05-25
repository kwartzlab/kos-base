<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        $reports = [
            [
                'name' => 'Member Status',
                'description' => 'A view of all members in the database and current membership status.',
                'route' => route('reports.member-status-report'),
                'permission' => 'view-user-reports',
            ],
            [
                'name' => 'Member Activity',
                'description' => 'A view of all gatekeeper authentications in the database by member.',
                'route' => route('reports.member-activity-report'),
                'permission' => 'view-gatekeeper-reports',
            ],
        ];

        $filtered_reports = [];

        foreach ($reports as $report) {
            // if no additional permissions are required or the user has the permission
            if (! isset($report['permission']) || \Gate::allows($report['permission'])) {
                $filtered_reports[] = $report;
            }
        }

        return view('reports.index', ['reports' => $filtered_reports]);
    }

    public function member_status_report()
    {
        $report_name = 'Member Status';
        $data = \App\Models\User::get();
        $fields = [
            ['name' => 'Name', 'callback' => function ($user) {
                return $user->get_name();
            }],
            ['name' => 'Email', 'callback' => function ($user) {
                return $user->email;
            }],
            ['name' => 'Status', 'callback' => function ($user) {
                return $user->status;
            }],
        ];

        return view('reports.tabular', [
            'report_name' => $report_name,
            'data' => $data,
            'fields' => $fields,
        ]);
    }

    public function member_activity_report(Request $request)
    {
        $report_name = 'Member Activity Report';

        if ($request->has('fromDate', 'toDate')) {
            $validated = $request->validate([
                'fromDate' => 'required|date',
                'toDate' => 'required|date',
            ]);

            $report_name = $report_name.' ('.$validated['fromDate'].' - '.$validated['toDate'].')';
            $data = \App\Models\Authentication::where('lock_in', '>=', $validated['fromDate'])
                ->where('lock_in', '<=', $validated['toDate'])
                ->orderBy('lock_in', 'desc')
                ->limit(5000) // an arbitrary limit above normal usage to reduce the risk of killing the database.
                ->get();

            $fields = [
                ['name' => 'Timestamp', 'callback' => function ($authentication) {
                    return $authentication->lock_in;
                }],
                ['name' => 'Name', 'callback' => function ($authentication) {
                    return ! is_null($authentication->user) ? $authentication->user->get_name() : '';
                }],
                ['name' => 'Gatekeeper', 'callback' => function ($authentication) {
                    return ! is_null($authentication->gatekeeper) ? $authentication->gatekeeper->name : '';
                }],
            ];

            return view('reports.tabular', [
                'report_name' => $report_name,
                'data' => $data,
                'fields' => $fields,
            ]);
        } else {
            $filters = ['daterange'];

            return view('reports.filter', [
                'report_name' => $report_name,
                'filters' => $filters,
            ]);
        }
    }
}
