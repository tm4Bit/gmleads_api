<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services;

class LeadDataMapper implements LeadDataMapperInterface
{
    public function mapToInsertable(array $leadFields, array $tableColumns): array
    {
        $dataToInsert = [];

        if (in_array('mom', $tableColumns)) {
            $dataToInsert['mom'] = date('Y-m-d H:i:s');
        }

        foreach ($leadFields as $key => $value) {
            $dataToInsert['make'] = 'Chevrolet';
            if ($key === 'bac') {
                $dataToInsert['dealer_code'] = $value;
            }
            if (in_array($key, $tableColumns)) {
                $dataToInsert[$key] = $value;
            }
        }

        unset($dataToInsert['id']);

        return $dataToInsert;
    }
}
