<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\UseCase;

use Core\Exception\HttpBadRequestException;
use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;
use Ovlk\GMLeads\Events\Repository\EventRepositoryInterface;
use Ovlk\GMLeads\Leads\DTO\LeadDTO;
use Ovlk\GMLeads\Leads\Repository\LeadRepositoryInterface;
use Ovlk\GMLeads\Leads\Services\LeadDataMapperInterface;
use PDOException;
use Psr\Log\LoggerInterface;
use Throwable;

class StoreLeadsUseCase
{
    private array $cachedTableColumns = [];

    private array $cachedEventDetails = [];

    private const HARDCODED_FIELDS_QUANTITY = 3;

    public function __construct(
        private LoggerInterface $logger,
        private LeadRepositoryInterface $leadRepository,
        private EventRepositoryInterface $eventRepository,
        private LeadDataMapperInterface $leadMapper
    ) {}

    public function execute(mixed $requestData): void
    {
        $leadsToProcess = $this->parseRequestPayload($requestData);
        $problematicLeadData = null;

        $this->leadRepository->beginTransaction();

        try {
            foreach ($leadsToProcess as $leadData) {
                $problematicLeadData = $leadData;

                $leadDto = LeadDTO::fromArray($leadData);

                $eventDetails = $this->getEventDetails($leadDto->briefingId);
                $targetTable = $eventDetails['tbl_clientes'];
                $validColumns = $this->getTableColumns($targetTable);

                $dataToInsert = $this->leadMapper->mapToInsertable($leadDto->fields, $validColumns);

                if (empty($dataToInsert) || (count($dataToInsert) <= self::HARDCODED_FIELDS_QUANTITY && isset($dataToInsert['mom']))) {
                    throw new HttpBadRequestException("Nenhum dado válido para inserir para o lead do briefing '{$leadDto->briefingId}'. Verifique os campos do payload.");
                }

                $this->leadRepository->save($targetTable, $dataToInsert);
            }

            $this->leadRepository->commit();
            $this->logger->info(count($leadsToProcess).' lead(s) processados e salvos com sucesso.');

        } catch (PDOException|HttpException|Throwable $e) {
            if ($this->leadRepository->inTransaction()) {
                $this->leadRepository->rollBack();
            }

            $this->logger->error('Falha ao processar lote de leads: '.$e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'problematic_lead' => $problematicLeadData,
            ]);

            if ($e instanceof HttpException) {
                throw $e;
            }
            throw new HttpException('Ocorreu um erro ao salvar os leads: '.$e->getMessage(), 500, 0, $e);
        }
    }

    /**
     * Normaliza o payload da requisição para sempre ser uma lista de leads.
     */
    private function parseRequestPayload(mixed $requestData): array
    {
        if (! is_array($requestData) || empty($requestData)) {
            throw new HttpBadRequestException('Payload inválido. Esperado um objeto de lead ou um array de leads.');
        }

        if (isset($requestData[0]) && is_array($requestData[0])) {
            return $requestData;
        }

        return [$requestData];
    }

    /**
     * Obtém as colunas de uma tabela, usando um cache interno para evitar consultas repetidas.
     */
    private function getTableColumns(string $tableName): array
    {
        if (! isset($this->cachedTableColumns[$tableName])) {
            $columnsData = $this->eventRepository->getTableColumns($tableName);
            if (empty($columnsData)) {
                $this->logger->error("Tabela '{$tableName}' não encontrada ou sem colunas definidas.");
                throw new HttpNotFoundException("Não foi possível obter colunas para a tabela de evento '{$tableName}'. A tabela existe?");
            }
            $this->cachedTableColumns[$tableName] = array_column($columnsData, 'Field');
        }

        return $this->cachedTableColumns[$tableName];
    }

    private function getEventDetails(int $briefingId)
    {

        if (! isset($this->cachedEventDetails[$briefingId])) {
            $eventDetails = $this->eventRepository->findById($briefingId);
            if (empty($eventDetails)) {
                $this->logger->error("Evento com ID '{$briefingId}' não encontrado.");
                throw new HttpNotFoundException("Evento para o briefing '{$briefingId}' não encontrado.");
            }
            $this->cachedEventDetails[$briefingId] = $eventDetails;
        }

        return $this->cachedEventDetails[$briefingId];
    }
}
