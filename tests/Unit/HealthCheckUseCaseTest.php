<?php

use Ovlk\GMLeads\HealthCheck\HealthCheckRepositoryInterface;
use Ovlk\GMLeads\HealthCheck\HealthCheckUseCase;
use Psr\Log\LoggerInterface;

it('should be able to fetch the database timezone', function () {
    $healthCheckRepository = Mockery::mock(HealthCheckRepositoryInterface::class);
    $healthCheckRepository->shouldReceive('getDatabaseTimeZone')
        ->once()
        ->andReturn('UTC');

    $logger = Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive('info')->never();

    $useCase = new HealthCheckUseCase($healthCheckRepository, $logger);

    $timezone = $useCase->execute();

    expect($timezone)->toBe('UTC');
});
