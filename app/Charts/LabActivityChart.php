<?php

namespace App\Charts;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Illuminate\Support\Facades\DB;

class LabActivityChart extends Chart
{
    public function __construct()
    {
        parent::__construct();

        $dates = CarbonPeriod::create(Carbon::now()->subMonth(), Carbon::now())->toArray();
        $chart_data = [];
        $chart_labels = [];
        foreach ($dates as $date) {
            $chart_data[$date->format('Y-m-d')] = 0;
            $chart_labels[] = $date->format('M j');
        }

        $attendance = DB::table('authentications')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->where('user_id', '>', 0)
            ->wherein('gatekeeper_id', config('kwartzlabos.entrance_gatekeepers'))
            ->get()
            ->groupby((function ($val) {
                return Carbon::parse($val->created_at)->format('Y-m-d');
            }));

        foreach ($attendance as $key => $daily) {
            $chart_data[$key] = $daily->unique('user_id')->count();
        }

        return $this->labels($chart_labels)
            ->options([
                'responsive' => true,
                'tooltips' => [
                    'enabled' => true,
                ],
                'maintainAspectRatio' => false,
                'legend' => ['display' => false],
                'scales' => [
                    'xAxes' => [[
                        'scaleLabel' => [
                            'display' => false,
                            'fontSize' => 14,
                        ],
                        'gridLines' => [
                            'display' => false,
                        ],
                    ]],
                    'yAxes' => [[
                        'scaleLabel' => [
                            'display' => false,
                            'fontSize' => 14,
                        ],
                        'gridLines' => [
                            'display' => false,
                        ],
                    ]],
                ],
                'datasets' => [
                    'line' => [
                        'backgroundColor' => '#3C8DBC',
                        'borderColor' => '#367FA9',
                    ],
                ],
            ])
            ->dataset('Visiting Members', 'line', array_values($chart_data));
    }
}
