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

        $countryInfo = $this->db->queryBuilder('SELECT * FROM c_paises WHERE id = :countryId', [
            'countryId' => $eventInfo['pais'],
        ])->find();

        $carModelField = 'carro_gm_'.strtolower($countryInfo['country_code']);

        $crmPayload = [];
        foreach ($leads as $lead) {
            $dedicatedKeys = ['nome', 'snome', 'doc', 'email', 'tel', 'cel', 'dealer_code', 'make'];

            if (array_key_exists($carModelField, $lead)) {
                $dedicatedKeys[] = $carModelField;
            }

            $extraData = array_diff_key($lead, array_flip($dedicatedKeys));

            $commentsParts = [];

            foreach ($extraData as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $commentsParts[] = "{$key}: {$value}";
            }

            $commentString = implode('|', $commentsParts);

            $crmPayload[] = [
                'supplier_code' => 'NSC', // NOTE: HARDCODED FIELD
                'source_system' => 'OPIE_NSC_MAN', // NOTE: HARDCODED FIELD
                'market_code' => $countryInfo['market_code'], // GMBR, GMAR, GMCO ...
                'country_code' => $countryInfo['country_code'], // BR, AR, CO
                'action_id' => $eventInfo['cod_evento'] ?? null,
                'content_type' => $eventInfo['content_type'] ?? null,
                'make' => $lead['make'] ?? null, // e.g., 'chevrolet'
                'model' => $lead[$carModelField] ?? null, // e.g., 'onix', 'tracker'
                'first_name' => $lead['nome'] ?? null,
                'last_name' => $lead['snome'] ?? null,
                'customer_id' => $lead['doc'] ?? null,
                'email_address' => $lead['email'] ?? null,
                'home_phone' => array_key_exists('tel', $lead) ? $this->formatCel($lead['tel'], $countryInfo['ddi']) : null,
                'cell_phone' => array_key_exists('cel', $lead) ? $this->formatCel($lead['cel'], $countryInfo['ddi']) : null,
                'dealer_code' => (int) $lead['dealer_code'] ?? null,
                'source' => 'BATCH', // NOTE: HARDCODED FIELD
                'comments' => $commentString, // e.g., "instagram: @fulano | pet: sim"
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
            $this->db->queryBuilder(
                'INSERT INTO crm (evento_id, evento_stop, `return`, mom) VALUES (:evento_id, :evento_stop, :return, NOW())',
                [
                    'evento_id' => $eventInfo['id'],
                    'evento_stop' => $lastSentId,
                    'return' => $e->getMessage(),
                ]
            );
            throw new HttpException('Erro ao enviar leads para o CRM: '.$e->getMessage(), $e->getCode());
        }
    }

    public function formatCel(?string $cel, string $ddi): ?string
    {
        $cel = preg_replace('/\D/', '', $cel); // Remove non-digit characters
        $cel = preg_replace('/^'.$ddi.'/', '', $cel); // Remove DDI if present

        return $ddi.substr($cel, 0, 2).substr($cel, 2);
    }
}
