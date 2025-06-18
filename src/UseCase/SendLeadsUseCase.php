<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\UseCase;

use Core\Database;
use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class SendLeadsUseCase
{
    private HttpClient $httpClient;

    public function __construct(private Database $db)
    {
        $base_uri = config('crm', 'endpoint');
        $this->httpClient = new HttpClient([
            'base_uri' => $base_uri,
            'timeout' => 10.0,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getCountryCode(string $countryNumber): string
    {
        $countryCodes = [
            '1' => 'BR',
            '2' => 'AR',
            '3' => 'CH',
            '4' => 'CO',
            '5' => 'PE',
            '6' => 'EQ',
        ];

        return $countryCodes[$countryNumber] ?? null;
    }

    public function execute(string $eventName)
    {
        $eventInfo = $this->db
            ->queryBuilder('SELECT * FROM briefing WHERE tbl_clientes = :eventName', ['eventName' => $eventName])
            ->find();

        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$eventName}' não encontrado no briefing.");
        }

        $leads = $this->db
            ->queryBuilder('SELECT * FROM :tableName', ['tableName' => $eventName])
            ->findAll();

        if (empty($leads)) {
            throw new HttpNotFoundException("Nenhum lead encontrado na tabela '{$eventName}'.");
        }

        $crmPayload = [];
        $countryNumber = $eventInfo['pais'];
        $countryCode = $this->getCountryCode($countryNumber);
        foreach ($leads as $lead) {
            // unset($lead['nome'], $lead['snome'], $lead['doc'], $lead['email'], $lead['tel'], $lead['cel']);
            $crmPayload[] = [
                'supplier_code' => 'NSC',
                'source_system' => 'OPIE_NSC_MAN',
                'market_code' => 'GM'.$countryCode,
                'country_code' => $countryCode,
                'action_id' => '90010',
                'content_type' => 'QUOTE',
                'make' => 'CHEVROLET',
                'model' => 'TRACKER',
                'first_name' => $lead['nome'],
                'last_name' => $lead['snome'],
                'customer_id' => $lead['doc'],
                'email_address' => $lead['email'],
                'home_phone' => $lead['tel'],
                'cell_phone' => $lead['cel'],
                'dealer_code' => 284871,
                'source' => 'BATCH',
                'comments' => "$lead",
            ];
        }

        try {
            $response = $this->httpClient->post('api/LeadsSiebel', [
                'json' => $crmPayload,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new HttpException('Erro ao enviar leads para o CRM: '.$response->getReasonPhrase());
            }
        } catch (GuzzleException $e) {
            throw new HttpException('Erro ao enviar leads para o CRM: '.$e->getMessage());
        }
    }
}
