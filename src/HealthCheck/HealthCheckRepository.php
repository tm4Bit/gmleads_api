<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\HealthCheck;

use Core\Database\Repository\Repository;

class HealthCheckRepository extends Repository implements HealthCheckRepositoryInterface
{
    public function getDatabaseTimeZone(): string
    {
        $result = $this->query('SELECT @@session.time_zone AS time_zone')
            ->fetch();

        if ($result && isset($result['time_zone'])) {
            return $result['time_zone'];
        }

        return 'UTC';
    }
}
