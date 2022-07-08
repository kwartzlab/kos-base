<?php

namespace App\Http\Controllers;

use App\User;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index', [
            'users' => User::all(),
        ]);
    }
}
