<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SampleJob implements ShouldQueue
{
    protected string $param1;
    protected string $param2;

    /**
     * Create a new job instance.
     * @param $param1
     * @param $param2
     */
    public function __construct($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('backgroundJobs')->info('[Completed] Job App\Jobs\SampleJob::handle - ');
        $logMessage = LogHelper::formatLogMessage('Executing', __CLASS__, 'handle', "Executing Sample Job with params: $this->param1, $this->param2");
        Log::channel('backgroundJobs')->info($logMessage);
    }
}
