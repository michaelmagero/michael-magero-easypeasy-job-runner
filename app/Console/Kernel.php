<?php

namespace App\Console;

use App\Console\Commands\RunJob;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        RunJob::class,
    ];
}
