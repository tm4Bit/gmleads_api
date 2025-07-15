<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services\Crm;

use Core\Facade\Config;
use GuzzleHttp\Client as HttpClient;

class CrmApiClient implements CrmApiClientInterface
{
    public function __construct(private HttpClient $httpClient) {}

    public function sendLeads(array $payload, string $token): string
    {
        $response = $this->httpClient->post('api/LeadsSiebel', [
            'json' => $payload,
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            'base_uri' => Config::get('crm.endpoint')
        ]);

        return $response->getBody()->getContents();
    }
}
