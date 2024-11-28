<?php

namespace App\Helpers;

class LogHelper
{
    public static function formatLogMessage(string $status, string $className, string $methodName, string $message = ''): string
    {
        return "[$status] Job $className::$methodName - $message";
    }
}
