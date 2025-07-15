<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\UseCase;

use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use Ovlk\GMLeads\Events\Repository\EventRepositoryInterface;
use Ovlk\GMLeads\Leads\Repository\LeadRepositoryInterface;
use Ovlk\GMLeads\Leads\Services\Crm\CrmApiClientInterface;
use Ovlk\GMLeads\Leads\Services\Crm\CrmAuthenticatorInterface;
use Ovlk\GMLeads\Leads\Services\Crm\LeadToCrmPayloadMapperInterface;
use Psr\Log\LoggerInterface;

class SendLeadsUseCase
{
    public function __construct(
        private LoggerInterface $logger,
        private EventRepositoryInterface $eventRepository,
        private LeadRepositoryInterface $leadRepository,
        private CrmAuthenticatorInterface $crmAuthenticator,
        private LeadToCrmPayloadMapperInterface $payloadMapper,
        private CrmApiClientInterface $crmApiClient
    ) {}

    public function execute(string $tableName): array
    {
        $eventInfo = $this->eventRepository->findByTableName($tableName);
        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$tableName}' não encontrado no briefing.");
        }

        $lastSentId = $this->leadRepository->findLastSentLeadId((int) $eventInfo['id']);
        $leads = $this->leadRepository->findUnsentLeads($tableName, $lastSentId);

        if (empty($leads)) {
            return ['message' => 'Nenhum lead novo para enviar.'];
        }

        $token = $this->crmAuthenticator->getAuthToken();

        $countryInfo = $this->eventRepository->findCountryInfo((int) $eventInfo['pais']);
        $crmPayload = $this->payloadMapper->map($leads, $eventInfo, $countryInfo);

        $newLastLeadId = end($leads)['id'];

        try {
            $responseBody = $this->crmApiClient->sendLeads($crmPayload, $token);

            $this->leadRepository->logCrmSubmission((int) $eventInfo['id'], $newLastLeadId, 'Success: '.$responseBody);

            $this->logger->info("Lote de leads para '{$tableName}' enviado com sucesso.");

            return json_decode($responseBody, true) ?? ['response' => $responseBody];

        } catch (GuzzleException $e) {
            $this->leadRepository->logCrmSubmission((int) $eventInfo['id'], $lastSentId, $e->getMessage());

            $this->logger->error('Erro ao enviar leads para o CRM.', ['error' => $e->getMessage()]);
            throw new HttpException('Erro ao enviar leads para o CRM: '.$e->getMessage(), $e->getCode());
        }
    }
}
