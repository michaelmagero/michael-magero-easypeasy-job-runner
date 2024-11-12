<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $logs = File::exists(storage_path('logs/background_jobs.log'))
            ? File::get(storage_path('logs/background_jobs.log'))
            : 'No job logs found';

        return Inertia::render('Dashboard', [
            'logs' => $logs
        ]);
    }
}
