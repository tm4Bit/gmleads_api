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
    public function __construct(private HttpClient $httpClient, private Database $db) {}

    private function getCountryCode(int $countryNumber): string
    {
        $countryCodes = [
            1 => 'BR',
            2 => 'AR',
            3 => 'CH',
            4 => 'CO',
            5 => 'PE',
            6 => 'EQ',
        ];

        return $countryCodes[$countryNumber] ?? null;
    }

    public function execute(string $tableName)
    {
        $eventInfo = $this->db
            ->queryBuilder('SELECT * FROM briefing WHERE tbl_clientes = :eventName', ['eventName' => $tableName])
            ->find();

        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$tableName}' não encontrado no briefing.");
        }

        $authResponse = $this->httpClient->post('/api/Token/Auth', [
            'json' => [
                'email' => config('crm', 'email'),
                'password' => config('crm', 'password'),
            ],
        ]);

        if ($authResponse->getStatusCode() !== 200) {
            throw new HttpException('Erro ao obter token de autenticação: '.$authResponse->getReasonPhrase());
        }

        $authBody = json_decode($authResponse->getBody()->getContents(), true);
        $token = $authBody['token'];
        $lastSentId = $eventInfo['crm'] ?? 0;

        $leads = $this->db
            ->queryBuilder("SELECT * FROM $tableName WHERE id > :lastSentId", [
                'lastSentId' => $lastSentId,
            ])
            ->findAll();

        if (empty($leads)) {
            return ['message' => 'Nenhum lead encontrado para enviar.'];
        }

        $crmPayload = [];
        $countryNumber = $eventInfo['pais'];
        $countryCode = $this->getCountryCode($countryNumber);
        foreach ($leads as $lead) {
            $crmPayload[] = [
                'supplier_code' => 'NSC',
                'source_system' => 'OPIE_NSC_MAN',
                'market_code' => 'GM'.$countryCode,
                'country_code' => $countryCode,
                'action_id' => '90010',
                'content_type' => 'QUOTE',
                'make' => 'CHEVROLET',
                'model' => 'TRACKER',
                'first_name' => $lead['nome'] ?? null,
                'last_name' => $lead['snome'] ?? null,
                'customer_id' => $lead['doc'] ?? null,
                'email_address' => $lead['email'] ?? null,
                'home_phone' => $lead['tel'] ?? null,
                'cell_phone' => $lead['cel'] ?? null,
                'dealer_code' => 284871,
                'source' => 'BATCH',
                'comments' => json_encode($lead, JSON_UNESCAPED_UNICODE),
            ];
        }

        try {
            $response = $this->httpClient->post('api/LeadsSiebel', [
                'json' => $crmPayload,
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]);

            $responseBody = $response->getBody()->getContents();

            if ($response->getStatusCode() !== 200) {
                throw new HttpException('Erro ao enviar leads para o CRM: '.$response->getReasonPhrase());
            }

            $newLastLeadId = array_key_last($leads) + 1;
            $this->db->queryBuilder('UPDATE briefing SET crm = :crm WHERE tbl_clientes = :eventName', [
                'crm' => $newLastLeadId,
                'eventName' => $tableName,
            ]);

            // NOTE: The table crm doesn't exist yet
            /* $this->db->queryBuilder(
                'INSERT INTO crm (evento_id, evento_stop, return, mom) VALUES (:evento_id, :evento_stop, :return, NOW())',
                [
                    'evento_id' => $eventInfo['id'],
                    'evento_stop' => $newLastLeadId,
                    'return' => $contentReturned,
                ]
            ); */

            return json_decode($responseBody, true);
        } catch (GuzzleException $e) {
            throw new HttpException('Erro ao enviar leads para o CRM: '.$e->getMessage(), $e->getCode());
        }
    }
}
