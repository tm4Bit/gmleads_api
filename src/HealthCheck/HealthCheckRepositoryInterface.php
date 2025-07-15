<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\HealthCheck;

interface HealthCheckRepositoryInterface
{
    public function getDatabaseTimeZone(): string;
}
