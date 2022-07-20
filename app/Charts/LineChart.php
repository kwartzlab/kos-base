<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class LineChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Set default options to apply to all charts
        $this->options([
            'responsive' => true,
            'chart' => [
                //'height' => '200'
            ],
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

        ]);
    }

    /*

    var option = {
      legend: {
            display: false,
          },
          layout: {
            padding: {
              left: 10,
              top: 10,
              right: 10,
              bottom: 10,
            },
          },
        scales: {
        yAxes:[{
            scaleLabel: {
                display: true,
                labelString: 'Y',
                fontFamily: 'Lato',
                fontSize: 14,
              },
              ticks: {
                beginAtZero: true,
                padding: 10,
                fontFamily: 'Lato',
                fontSize: 14,
              },
              afterBuildTicks: getYTicks(),
              gridLines: {
                display: true,
                color: 'rgba(50,50,50,0.2)',
                zeroLineColor: 'rgba(50,50,50,0.5)',
                zeroLineWidth: 2,
                drawTicks: true,
                tickMarkLength: 4,
              },
        }],
        xAxes:[{
          barPercentage: 0.8,
          categoryPercentage: 1,
          gridLines: {
                display: false,
                tickMarkLength: 10,
                color: 'rgba(50,50,50,0.1)',
                drawTicks: false,
                offsetGridLines: false,
                zeroLineColor: 'rgba(50,50,250,0.5)',
                zeroLineWidth: 5,
          },
          scaleLabel: {
                display: true,
                labelString: 'Pages',
                padding: 20,
                fontFamily: 'Lato',
                fontSize: 14,
              },
          ticks: {
                beginAtZero: true,
                callback: formatz,
                fontFamily: 'Lato',
                fontSize: 14,
              },
        }]
      }
    };
    */
}
