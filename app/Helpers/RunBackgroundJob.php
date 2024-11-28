<?php

namespace App\Helpers;

use App\Services\BackgroundJobService;
use Illuminate\Support\Facades\Log;

function RunBackgroundJob(string $className, string $methodName, array $parameters = []): bool
{
    $backgroundJobService = app(BackgroundJobService::class);

    try {
        $backgroundJobService->executeBackgroundJob($className, $methodName, $parameters);

        $serializedParameters = base64_encode(serialize($parameters));
        $command = "php artisan run:job \"$className\" \"$methodName\" \"$serializedParameters\"";

        if (PHP_OS_FAMILY === 'Windows') {
            $command = "start /B $command";
        } else {
            $command = "nohup $command > /dev/null 2>&1 &";
        }

        exec($command);
        return true;
    } catch (Exception $e) {
        Log::channel('BackgroundJobsErrors')->error("Failed to run background job " . $e->getMessage());
        return false;
    }
}
