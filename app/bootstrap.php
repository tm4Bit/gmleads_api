<?php

declare(strict_types=1);

use Core\Facade\Config;
use Core\Facade\LoggerFactory;
use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use Ovlk\GMLeads\Events\Repository\EventRepository;
use Ovlk\GMLeads\Events\Repository\EventRepositoryInterface;
use Ovlk\GMLeads\HealthCheck\HealthCheckRepository;
use Ovlk\GMLeads\HealthCheck\HealthCheckRepositoryInterface;
use Ovlk\GMLeads\Leads\Repository\LeadRepository;
use Ovlk\GMLeads\Leads\Repository\LeadRepositoryInterface;
use Ovlk\GMLeads\Leads\Services\Crm\CrmApiClient;
use Ovlk\GMLeads\Leads\Services\Crm\CrmApiClientInterface;
use Ovlk\GMLeads\Leads\Services\Crm\CrmAuthenticator;
use Ovlk\GMLeads\Leads\Services\Crm\CrmAuthenticatorInterface;
use Ovlk\GMLeads\Leads\Services\Crm\LeadToCrmPayloadMapper;
use Ovlk\GMLeads\Leads\Services\Crm\LeadToCrmPayloadMapperInterface;
use Ovlk\GMLeads\Leads\Services\LeadDataMapper;
use Ovlk\GMLeads\Leads\Services\LeadDataMapperInterface;
use Psr\Log\LoggerInterface;

use function DI\autowire;

return function (ContainerBuilder $container) {
    $container->addDefinitions([
        LoggerInterface::class => fn () => LoggerFactory::create(),
        HttpClient::class => function () {
            $base_uri = Config::get('crm.endpoint');

            return new HttpClient([
                'base_uri' => $base_uri,
                'timeout' => 10.0,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        },

        // Definições de Repositório existentes
        EventRepositoryInterface::class => autowire(EventRepository::class),
        LeadRepositoryInterface::class => autowire(LeadRepository::class),
        HealthCheckRepositoryInterface::class => autowire(HealthCheckRepository::class),

        // --- Adicione estas definições de serviço ---
        CrmAuthenticatorInterface::class => autowire(CrmAuthenticator::class),
        LeadToCrmPayloadMapperInterface::class => autowire(LeadToCrmPayloadMapper::class),
        CrmApiClientInterface::class => autowire(CrmApiClient::class),

        LeadDataMapperInterface::class => autowire(LeadDataMapper::class),
    ]);

    return $container;
};
