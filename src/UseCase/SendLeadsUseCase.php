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

    private function getCountryCode(int $countryNumber): ?string
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

    public function execute(string $tableName): ?array
    {
        $eventInfo = $this->db
            ->queryBuilder('SELECT * FROM briefing WHERE tbl_clientes = :eventName', ['eventName' => $tableName])
            ->find();

        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$tableName}' não encontrado no briefing.");
        }

        $lastCrmEntry = $this->db
            ->queryBuilder('SELECT evento_stop FROM crm WHERE evento_id = :evento_id ORDER BY mom DESC LIMIT 1', [
                'evento_id' => $eventInfo['id'],
            ])
            ->find();

        $lastSentId = $lastCrmEntry ? $lastCrmEntry['evento_stop'] : 0;

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

        $leads = $this->db
            ->queryBuilder("SELECT * FROM `$tableName` WHERE id > :lastSentId ORDER BY id ASC", [
                'lastSentId' => $lastSentId,
            ])
            ->findAll();

        if (empty($leads)) {
            return ['message' => 'Nenhum lead novo para enviar.'];
        }

        $crmPayload = [];
        $countryNumber = $eventInfo['pais'];
        $countryCode = $this->getCountryCode($countryNumber);
        $carModelField = $countryCode !== null ? 'carro_gm_'.strtolower($countryCode) : null;

        foreach ($leads as $lead) {
            $commentField = json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $crmPayload[] = [
                'supplier_code' => 'NSC', // NOTE: HARDCODED FIELD
                'source_system' => 'OPIE_NSC_MAN', // NOTE: HARDCODED FIELD
                'market_code' => 'GM'.$countryCode, // GMBR, GMAR, GMCO ...
                'country_code' => $countryCode, // BR, AR, CO
                'action_id' => $eventInfo['cod_evento'] ?? null,
                'content_type' => $eventInfo['content_type'] ?? null,
                'make' => $lead['carro_marca'] ?? null, // e.g., 'chevrolet'
                'model' => $lead[$carModelField] ?? null, // e.g., 'onix', 'tracker'
                'first_name' => $lead['nome'] ?? null,
                'last_name' => $lead['snome'] ?? null,
                'customer_id' => $lead['doc'] ?? null,
                'email_address' => $lead['email'] ?? null,
                'home_phone' => $lead['tel'] ?? null,
                'cell_phone' => $lead['cel'] ?? null,
                'dealer_code' => $lead['dealer_code'] ?? null,
                'source' => 'BATCH', // NOTE: HARDCODED FIELD
                'comments' => "$commentField", // e.g., 'Lead enviado via OPIE'
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

            $lastLeadInBatch = end($leads);
            $newLastLeadId = $lastLeadInBatch['id'];

            $this->db->queryBuilder(
                'INSERT INTO crm (evento_id, evento_stop, `return`, mom) VALUES (:evento_id, :evento_stop, :return, NOW())',
                [
                    'evento_id' => $eventInfo['id'],
                    'evento_stop' => $newLastLeadId,
                    'return' => $responseBody,
                ]
            );

            return json_decode($responseBody, true);
        } catch (GuzzleException $e) {
            throw new HttpException('Erro ao enviar leads para o CRM: '.$e->getMessage(), $e->getCode());
        }
    }
}
