<?php

declare(strict_types = 1);

namespace App\Charts;

use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;


class LabActivityChart extends BaseChart
{

    public ?array $middlewares = ['auth'];

    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        // generate date range
        $dates = CarbonPeriod::create(Carbon::now()->subMonth(), Carbon::now())->toArray();
        $chart_data = [];
        $chart_labels = [];
        foreach ($dates as $date) {
            $chart_data[$date->format('Y-m-d')] = 0;
            $chart_labels[] = $date->format('M j');
        }

        // get attendance records
        $attendance = DB::table('authentications')
                        ->where('created_at', '>=', Carbon::now()->subMonth())
                        ->where('user_id', '>', 0)
                        ->wherein('gatekeeper_id', config('kwartzlabos.entrance_gatekeepers'))
                        ->get()
                        ->groupby((function ($val) {
                            return Carbon::parse($val->created_at)->format('Y-m-d');
                        }));

        // compile daily attendance count
        $daily_attendance = [];
        foreach ($attendance as $key => $daily) {
            $chart_data[$key] = $daily->unique('user_id')->count();
        }

        return Chartisan::build()
            ->labels($chart_labels)
            ->dataset("Visiting Members", array_values($chart_data));

        }
}