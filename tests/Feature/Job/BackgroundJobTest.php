<?php

use App\Helpers\runBackgroundJob;
use Illuminate\Support\Facades\Log;

test('background job can be ran', function () {
    //$result = runBackgroundJob('App\Jobs\SampleJob', 'handle',  ['value1', 'value2']);
    $result = RunBackgroundJob::runJob('App\Jobs\SampleJob', 'handle', ['value1', 'value2']);

    Log::shouldReceive('info')
        ->with('Executing Sample Job with params: value1, value2');

    $this->assertNotFalse($result, "Failed to run background job");

    $this->assertFileExists(storage_path('logs/backgroundJobs.log'), "Job log file missing");
});

