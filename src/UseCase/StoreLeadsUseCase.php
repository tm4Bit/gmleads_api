<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\UseCase;

use Core\Database;
use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;
use PDOException;
use Throwable;

class StoreLeadsUseCase
{
    private array $cachedTableColumns = [];

    public function __construct(private Database $db) {}

    public function execute(mixed $requestData): void
    {
        $leadsToProcess = [];
        $currentLeadDataForErrorLog = null;

        if (isset($requestData[0]) && is_array($requestData[0])) {
            $leadsToProcess = $requestData;
        } elseif (is_array($requestData) && ! empty($requestData)) {
            $leadsToProcess[] = $requestData;
        } else {
            throw new HttpException('Payload inválido. Esperado um objeto de lead ou um array de leads.', 400);
        }

        if (empty($leadsToProcess)) {
            throw new HttpException('Nenhum lead fornecido para processamento.', 400);
        }

        $this->db->beginTransaction();

        try {
            foreach ($leadsToProcess as $index => $leadData) {
                $currentLeadDataForErrorLog = $leadData;

                if (! is_array($leadData)) {
                    throw new HttpException("Item de lead na posição {$index} não é um objeto válido.", 400);
                }

                $briefingId = $leadData['briefing'] ?? null;
                if (! $briefingId) {
                    throw new HttpException('O campo "briefing" é obrigatório em cada lead.', 400);
                }

                $eventDetails = $this->db
                    ->queryBuilder('SELECT id, tbl_clientes, pais AS briefing_pais_id FROM briefing WHERE id = :briefingId', [
                        ':briefingId' => $briefingId,
                    ])->find();

                if (! $eventDetails) {
                    throw new HttpNotFoundException("Evento para o briefing '{$briefingId}' não encontrado.", 404);
                }

                if (! isset($leadData['ckreg']) || ($leadData['ckreg'] !== '1' && $leadData['ckreg'] !== 1 && $leadData['ckreg'] !== true)) {
                    throw new HttpException("Você deve aceitar os termos de uso e a política de privacidade para o lead do briefing '{$briefingId}'.", 400);
                }

                $targetTable = $eventDetails['tbl_clientes'];
                if (! isset($this->cachedTableColumns[$targetTable])) {
                    $tableColumnsResult = $this->db->queryBuilder("SHOW COLUMNS FROM `{$targetTable}`")->findAll();
                    if (empty($tableColumnsResult)) {
                        throw new HttpNotFoundException("Não foi possível obter colunas para a tabela de evento '{$targetTable}'.");
                    }
                    $this->cachedTableColumns[$targetTable] = array_column($tableColumnsResult, 'Field');
                }
                $targetTableColumns = $this->cachedTableColumns[$targetTable];

                $dataToInsert = [];
                if (in_array('mom', $targetTableColumns)) {
                    $dataToInsert['mom'] = date('Y-m-d');
                }

                foreach ($leadData as $key => $value) {
                    if (in_array($key, ['briefing', 'ckreg', 'cf-turnstile-response', 'sub2'])) {
                        continue;
                    }

                    if ($key === 'country') {
                        if (in_array('pais', $targetTableColumns)) {
                            $dataToInsert['pais'] = $value;
                        }

                        continue;
                    }

                    if (in_array($key, $targetTableColumns)) {
                        $dataToInsert[$key] = $value;
                    }
                }

                if (array_key_exists('id', $dataToInsert)) {
                    unset($dataToInsert['id']);
                }

                if (empty($dataToInsert) || (count($dataToInsert) === 1 && isset($dataToInsert['mom']) && count($targetTableColumns) > 1)) {
                    throw new HttpException("Nenhum dado válido para inserir para o lead do briefing '{$briefingId}' na tabela '{$targetTable}'. Verifique se os nomes dos campos no payload correspondem às colunas da tabela e se há dados além de 'mom'.", 400);
                }

                $insertColumns = array_keys($dataToInsert);
                $insertValues = array_values($dataToInsert);
                $placeholders = array_map(fn ($column) => ":{$column}", $insertColumns);

                $columnsSql = implode(', ', array_map(fn ($col) => "`{$col}`", $insertColumns));
                $placeholdersSql = implode(', ', $placeholders);

                $insertQuery = "INSERT INTO `{$targetTable}` ({$columnsSql}) VALUES ({$placeholdersSql})";

                $this->db->queryBuilder($insertQuery, array_combine($placeholders, $insertValues));
            }

            $this->db->commit();

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro PDO ao inserir leads: {$e->getMessage()}. Lead problemático (parcial): ".json_encode($currentLeadDataForErrorLog));
            throw new HttpException('Erro ao inserir os dados no banco de dados: '.$e->getMessage(), 500);
        } catch (HttpException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro inesperado ao processar leads: {$e->getMessage()}. Lead problemático (parcial): ".json_encode($currentLeadDataForErrorLog));
            throw new HttpException('Ocorreu um erro inesperado durante o processamento dos leads: '.$e->getMessage(), 500);
        }
    }
}
