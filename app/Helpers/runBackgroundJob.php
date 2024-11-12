<?php

use App\Services\BackgroundJobService;

function runBackgroundJob(string $className, string $methodName, array $parameters = []): void
{
    $backgroundJobService = app(BackgroundJobService::class);
    $backgroundJobService->executeBackgroundJob($className, $methodName, $parameters);

    $serializedParameters = base64_encode(serialize($parameters));

    $command = "php artisan run:job \"$className\" \"$methodName\" \"$serializedParameters\"";

    if (PHP_OS_FAMILY === 'Windows') {
        $command = "start /B $command";
    } else {
        $command = "nohup $command > /dev/null 2>&1 &";
    }

    exec($command);
}
