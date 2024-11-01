<?php

namespace App\Http\Controllers;

class ReportsController extends Controller
{
    public function index()
    {
        $reports = [
            [
                'name' => 'Member Status',
                'description' => 'A view of all members in the database and current membership status.',
                'route' => route('reports.member-status-report'),
            ],
        ];

        return view('reports.index', ['reports' => $reports]);
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
}
