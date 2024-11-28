<?php

use App\Services\BackgroundJobService;
use App\Jobs\SampleJob;
use Illuminate\Support\Facades\Log;
use function App\Helpers\RunBackgroundJob;

beforeEach(function () {
    // Ensure that we clear the logs before each test
    Log::shouldReceive('channel')->once()->with('backgroundJobsErrors');
});

it('executes a background job successfully', function () {
    // Mock the BackgroundJobService
    $backgroundJobServiceMock = Mockery::mock(BackgroundJobService::class);
    $backgroundJobServiceMock->shouldReceive('executeBackgroundJob')
        ->once()
        ->with(SampleJob::class, 'handle', ['param1', 'param2'])
        ->andReturn(true);

    // Simulate calling the RunBackgroundJob helper function
    $result = RunBackgroundJob(SampleJob::class, 'handle', ['param1', 'param2']);

    // Assert the result is true (indicating success)
    expect($result)->toBeTrue();
});

it('logs an error when the background job fails', function () {
    $backgroundJobServiceMock = Mockery::mock(BackgroundJobService::class);
    $backgroundJobServiceMock->shouldReceive('executeBackgroundJob')
        ->once()
        ->with(SampleJob::class, 'handle', ['param1', 'param2'])
        ->andThrow(new Exception('Job failed'));

    Log::shouldReceive('channel')
        ->with('backgroundJobsErrors')
        ->once()
        ->andReturnSelf();
    Log::shouldReceive('error')
        ->with('Failed to run background job Job failed')
        ->once();

    $result = RunBackgroundJob(SampleJob::class, 'handle', ['param1', 'param2']);

    expect($result)->toBeFalse();
});
