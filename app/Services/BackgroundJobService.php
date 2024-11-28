<?php

namespace App\Services;

use App\Helpers\LogHelper;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

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
//    public function executeBackgroundJob(string $className, string $methodName, array $params = []): void
//    {
//        if (!class_exists($className) || !method_exists($className, $methodName)) {
//            $this->logBackgroundJobStatus($className, $methodName, 'Failed', 'Class or Method does not exist');
//            return;
//        }
//
//        $processId = pcntl_fork();
//
//        if ($processId == -1) {
//            $this->logBackgroundJobStatus($className, $methodName, 'failed', 'Failed to start Job process');
//        } else if ($processId) {
//            DB::disconnect();
//            DB::reconnect();
//
//            pcntl_wait($status);
//        } else {
//            try {
//                $instance = new $className(...$params);
//                call_user_func_array([$instance, $methodName], $params);
//                $this->logBackgroundJobStatus($className, $methodName, 'Completed');
//            } catch (Exception $e) {
//                $this->logBackgroundJobStatus($className, $methodName, 'Failed', $e->getMessage());
//                $this->retryBackgroundJob($className, $methodName, $params);
//            }
//            exit(0);
//        }
//    }

    public function executeBackgroundJob(string $className, string $methodName, array $params = []): void
    {
        if (!class_exists($className) || !method_exists($className, $methodName)) {
            $this->logBackgroundJobStatus($className, $methodName, 'Failed', 'Class or method does not exist');
            return;
        }

        try {
            if (is_subclass_of($className, ShouldQueue::class)) {
                // Dispatch the job if it implements ShouldQueue
                Queue::push(new $className(...$params));
                $this->logBackgroundJobStatus($className, $methodName, 'Queued');
            } else {
                // Use pcntl_fork for concurrent non-queueable jobs
                $processId = pcntl_fork();

                if ($processId == -1) {
                    // Fork failed
                    $this->logBackgroundJobStatus($className, $methodName, 'Failed', 'Failed to start job process');
                } elseif ($processId) {
                    // Parent process: disconnect and reconnect DB
                    DB::disconnect();
                    DB::reconnect();
                    pcntl_wait($status); // Wait for child process to finish
                } else {
                    // Child process: execute the job
                    try {
                        $instance = new $className(...$params);
                        call_user_func_array([$instance, $methodName], $params);
                        $this->logBackgroundJobStatus($className, $methodName, 'Completed');
                    } catch (Exception $e) {
                        $this->logBackgroundJobStatus($className, $methodName, 'Failed', $e->getMessage());
                        $this->retryBackgroundJob($className, $methodName, $params);
                    }
                    exit(0); // Ensure the child process exits after job execution
                }
            }
        } catch (Exception $e) {
            $this->logBackgroundJobStatus($className, $methodName, 'Failed', $e->getMessage());
            $this->retryBackgroundJob($className, $methodName, $params);
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
        $logMessage = LogHelper::formatLogMessage($status, $className, $methodName, $message);
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

//    /**
//     * Adds options to set delay and assign a level of priority to a job
//     * @param string $className
//     * @param string $methodName
//     * @param array $params
//     * @param int $delay
//     * @param string $priority
//     * @return void
//     */
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
