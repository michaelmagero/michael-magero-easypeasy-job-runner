<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackgroundJobService
{
    protected int $maxAttempts = 3;
    protected int $retryAfter = 60;

    /**
     * Initiates a class execution in the background
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @return void
     */
    public function executeBackgroundJob(string $className, string $methodName, array $params = []): void
    {
        if (!class_exists($className) || !method_exists($className, $methodName)) {
            $this->logBackgroundJobStatus($className, $methodName, 'Failed', 'Class or Method does not exist');
        }

        $processId = pcntl_fork();

        if ($processId == -1) {
            $this->logBackgroundJobStatus($className, $methodName, 'failed', 'Failed to start Job process');
        } else if ($processId) {
            DB::disconnect();
            DB::reconnect();

            pcntl_wait($status);
        } else {
            try {
                $instance = new $className(...$params);
                call_user_func_array([$instance, $methodName], $params);
                $this->logBackgroundJobStatus($className, $methodName, 'Completed');
            } catch (Exception $e) {
                $this->logBackgroundJobStatus($className, $methodName, 'Failed', $e->getMessage());
                $this->retryBackgroundJob($className, $methodName, $params);
            }
            exit(0);
        }
    }

    /**
     * Outputs the status of a job in the log file
     *
     * @param string $className
     * @param string $methodName
     * @param string $status
     * @param string $message
     * @return void
     */
    private function logBackgroundJobStatus(string $className, string $methodName, string $status, string $message = ''): void
    {
        $logMessage = "[$status] Job $className::$methodName - $message";
        Log::channel('backgroundJobs')->info($logMessage);

        if ($status === 'failed') {
            Log::channel('backgroundJobsErrors')->error($logMessage);
        }
    }

    /**
     * Retries Jobs as per the number of attempts set and for the set delay duration
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @return void
     */
    private function retryBackgroundJob(string $className, string $methodName, array $params): void
    {
        $attempt = 1;
        while ($attempt < $this->maxAttempts) {
            sleep($this->retryAfter);

            try {
                $instance = new $className();
                call_user_func_array([$instance, $methodName], $params);
                $this->logBackgroundJobStatus($className, $methodName, 'Completed on retry'. $attempt);
                return;
            } catch (Exception $e) {
                $this->logBackgroundJobStatus($className, $methodName, 'Failed on retry' . $attempt, $e->getMessage());
                $attempt++;
            }
        }
    }

    /**
     * Adds options to set delay and assign a level of priority to a job
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param int $delay
     * @param string $priority
     * @return void
     */
//    public function backgroundJobDelayAndPriority(string $className, string $methodName, array $params, int $delay = 0, string $priority = 'high'): void
//    {
//        if ($delay > 0) {
//            sleep($delay);
//        }
//
//        if ($priority == 'high') {
//            $delay = 0;
//        }
//
//        if ($priority == 'low') {
//            $delay = 5;
//        }
//
//    }
}
