<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:job {className} {methodName} {params}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the Background Jobs';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $className = $this->argument('className');
        $methodName = $this->argument('methodName');
        $params = unserialize(base64_decode($this->argument('params')));

        try {
            if (class_exists($className) && method_exists($className, $methodName)) {
                $instance = app($className, $params);
                call_user_func_array([$instance, $methodName], $params);

                Log::channel('backgroundJobs')->info("Background Job $className::$methodName run successfully.");
            } else {
                throw new Exception("Class or Method does not exist.");
            }
        } catch (Exception $e) {
            Log::channel('backgroundJobsErrors')->error("Failed to execute job" . $e->getMessage());
        }
    }
}
