<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services\Crm;

use Core\Facade\Config;

class LeadToCrmPayloadMapper implements LeadToCrmPayloadMapperInterface
{
    public function map(array $leads, array $eventInfo, array $countryInfo): array
    {
        $crmPayload = [];
        $carModelField = 'carro_gm_'.strtolower($countryInfo['country_code']);
        $defaults = Config::get('crm.defaults');

        foreach ($leads as $lead) {
            $crmPayload[] = $this->mapSingleLead($lead, $eventInfo, $countryInfo, $carModelField, $defaults);
        }

        return $crmPayload;
    }

    private function mapSingleLead(array $lead, array $eventInfo, array $countryInfo, string $carModelField, array $defaults): array
    {
        return [
            'supplier_code' => $defaults['supplier_code'],
            'source_system' => $defaults['source_system'],
            'market_code' => $countryInfo['market_code'],
            'country_code' => $countryInfo['country_code'],
            'action_id' => $eventInfo['cod_evento'] ?? null,
            'content_type' => $eventInfo['content_type'] ?? null,
            'make' => $lead['make'] ?? null,
            'model' => $lead[$carModelField] ?? null,
            'first_name' => $lead['nome'] ?? null,
            'last_name' => $lead['snome'] ?? null,
            'customer_id' => $lead['doc'] ?? null,
            'email_address' => $lead['email'] ?? null,
            'home_phone' => array_key_exists('tel', $lead) ? $this->formatCel($lead['tel'], $countryInfo['ddi']) : null,
            'cell_phone' => array_key_exists('cel', $lead) ? $this->formatCel($lead['cel'], $countryInfo['ddi']) : null,
            'dealer_code' => isset($lead['dealer_code']) ? (int) $lead['dealer_code'] : null,
            'source' => $defaults['source'],
            'comments' => $this->buildComments($lead, $carModelField),
        ];
    }

    private function buildComments(array $lead, string $carModelField): string
    {
        $dedicatedKeys = ['id', 'nome', 'snome', 'doc', 'email', 'tel', 'cel', 'dealer_code', 'make', $carModelField];
        $extraData = array_diff_key($lead, array_flip($dedicatedKeys));

        $commentsParts = [];
        foreach ($extraData as $key => $value) {
            if ($value === null || $value === '' || $key === 'k') {
                continue;
            }
            $commentsParts[] = "{$key}: {$value}";
        }

        return implode('|', $commentsParts);
    }

    private function formatCel(?string $cel, string $ddi): ?string
    {
        if ($cel === null) {
            return null;
        }

        $cel = preg_replace('/\D/', '', $cel);
        if (str_starts_with($cel, $ddi)) {
            $cel = substr($cel, strlen($ddi));
        }

        return $ddi.$cel;
    }
}
