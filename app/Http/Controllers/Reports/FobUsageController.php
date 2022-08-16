<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;

class FobUsageController extends Controller
{
    public function show()
    {
        return view('reports.fob_usage');
    }
}
