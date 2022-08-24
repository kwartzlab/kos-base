<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;

class DashboardController extends Controller
{
    public function index()
    {

        // compile latest new members
        $latest_members = \App\User::where(['status' => 'active'])->orderby('date_admitted', 'desc')->orderby('first_preferred')->limit(5)->get();

        // compile latest applicants
        $latest_applicants = \App\User::where(['status' => 'applicant'])->orderby('date_applied', 'desc')->orderby('first_preferred')->limit(5)->get();

        // get active gatekeepers that user is authorized for
        $gatekeepers = \App\Gatekeeper::where(['status' => 'enabled', 'type' => 'lockout'])->orderby('name')->get();

        /* EVENTS CALENDAR */
        $events = Event::get(Carbon::now(), Carbon::now()->addDay(14));
        $events = $events->groupby((function ($val) {
            if ($val->start->date == null) {
                return Carbon::parse($val->startDateTime)->format('Y-m-d');
            } else {
                return Carbon::parse($val->startDate)->format('Y-m-d');
            }
        }));
        $events = $events->slice(0, 9);

        return view('dashboard.index', compact('latest_members', 'latest_applicants', 'gatekeepers', 'events'));
    }
}
