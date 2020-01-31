<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use App\Charts\LineChart;
use Spatie\GoogleCalendar\Event;

class DashboardController extends Controller
{

    public function index() {

        // compile latest new members
        $latest_members = \App\User::where(['status' => 'active'])->orderby('date_admitted','desc')->orderby('first_name')->limit(5)->get();

        // compile latest applicants
        $latest_applicants = \App\User::where(['status' => 'applicant'])->orderby('date_admitted','desc')->orderby('first_name')->limit(5)->get();

        // get active gatekeepers that user is authorized for
        $gatekeepers = \App\Gatekeeper::where(['status' => 'enabled', 'type' => 'lockout'])->orderby('name')->get();


        /* ATTENDANCE CHART */
        // setup the attendance chart
        $attendance_chart = new LineChart;
        //$attendance_chart->height(300);

        // get our date range for the chart
        $dates = CarbonPeriod::create(Carbon::now()->subMonth(), Carbon::now())->toArray();
        $chart_data = array();
        $chart_labels = array();
        foreach ($dates as $date){
            $chart_data[$date->format('Y-m-d')] = 0;
            $chart_labels[] = $date->format('M j');
        }

        $attendance_chart->labels($chart_labels);
        // grab our attendance records and add the daily counts to the chart_data array
        $attendance = DB::table('authentications')
                        ->where('created_at', '>=', Carbon::now()->subMonth())
                        ->where('user_id', '>', 0)
                        ->wherein('gatekeeper_id', config('kwartzlabos.entrance_gatekeepers'))
                        ->get()
                        ->groupby((function ($val) {
                            return Carbon::parse($val->created_at)->format('Y-m-d');
                        }));

        $daily_attendance = array();
        foreach($attendance as $key => $daily) {
            $chart_data[$key] = $daily->unique('user_id')->count();
        }

        $attendance_chart->dataset('Visiting Members','line', array_values($chart_data))
                         ->color('rgba(60,141,188,0.9)')
                         ->backgroundcolor('rgba(60,141,188,0.9)');

        /* EVENTS CALENDAR */
        $events = Event::get(Carbon::now(),Carbon::now()->addDay(14));
        $events = $events->groupby((function ($val) {
            if ($val->start->date == NULL) {
                return Carbon::parse($val->startDateTime)->format('Y-m-d');
            } else {
                return Carbon::parse($val->startDate)->format('Y-m-d');
            }
        }));
        $events = $events->slice(0,9);


        return view('dashboard.index',compact('latest_members','latest_applicants','gatekeepers','attendance_chart','events'));

    }
}
