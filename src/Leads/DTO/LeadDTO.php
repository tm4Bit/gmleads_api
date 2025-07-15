<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\DTO;

use Core\Exception\HttpBadRequestException;

final class LeadDTO
{
    /**
     * @param  int  $briefingId  O ID do evento/briefing.
     * @param  array  $fields  Os campos de dados do lead (nome, email, doc, etc.).
     */
    public function __construct(
        public readonly int $briefingId,
        public readonly array $fields,
    ) {}

    public static function fromArray(array $data): self
    {
        $briefingId = $data['briefing'] ?? null;
        if (empty($briefingId) || ! is_numeric($briefingId)) {
            throw new HttpBadRequestException('O campo "briefing" é obrigatório e deve ser um número.');
        }

        // $termsAccepted = $data['ckreg'] ?? null;
        // if ($termsAccepted !== '1' && $termsAccepted !== 1 && $termsAccepted !== true) {
        //     throw new HttpBadRequestException('A aceitação dos termos (ckreg) é obrigatória para o lead do briefing '.$briefingId);
        // }

        unset($data['briefing'], $data['ckreg'], $data['cf-turnstile-response'], $data['sub2']);

        return new self((int) $briefingId, $data);
    }
}
