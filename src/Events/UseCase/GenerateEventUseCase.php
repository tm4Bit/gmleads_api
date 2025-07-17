<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\UseCase;

use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;
use Ovlk\GMLeads\Events\Repository\EventRepositoryInterface;
use Ovlk\GMLeads\Events\Services\GithubServiceInterface;

class GenerateEventUseCase
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private GithubServiceInterface $githubService
    ) {}

    public function execute(string $tableName)
    {
        $eventInfo = $this->eventRepository->findByTableName($tableName);
        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$tableName}' não encontrado no briefing.");
        }
        $countryInfo = $this->eventRepository->findCountryInfo($eventInfo['pais']);
        $countryCode = $countryInfo['country_code'];

        $columns = $this->eventRepository->getTableColumns($tableName);
        if (empty($columns)) {
            throw new HttpNotFoundException("A tabela '{$tableName}' não foi encontrada ou não possui colunas.");
        }

        $htmlTemplate = $this->eventRepository->getHtmlTemplate($eventInfo['tipo_evento']);
        if (empty($htmlTemplate)) {
            throw new HttpException('Nenhum template HTML encontrado para este tipo de evento e nenhum template padrão definido.');
        }

        $eventSlug = str_replace('_', '-', $eventInfo['tbl_clientes']);
        $formHtml = $this->generateFormHtml($columns, $eventInfo, $countryCode);

        // Substituir os placeholders no template
        $finalHtml = str_replace(
            ['{%banner%}', '{%logo%}', '{%form%}'],
            [$eventInfo['bg_desk'], 'URL_DO_LOGO_PADRAO', $formHtml],
            $htmlTemplate
        );

        $filePathInRepo = sprintf('%s/%s/index.html', strtolower($countryCode), $eventSlug);
        $commitMessage = sprintf('Evento `%s` publicado!', $eventInfo['nome_evento']);
        $publicUrl = sprintf('https://leadshowgm.com/%s/%s', strtolower($countryCode), $eventSlug);
		
        $this->githubService->createOrUpdateFile($filePathInRepo, $finalHtml, $commitMessage);

        return [
            'success' => true,
            'message' => 'Evento publicado com sucesso no GitHub.',
            'event_url' => $publicUrl,
        ];
    }

    private function generateFormHtml(array $columns, array $eventInfo, string $countryCode): string
    {
        $formHtml = base_path('templates/form.php');
        $translations = base_path('translations/'.strtolower($countryCode).'.json');
        if (! file_exists($translations)) {
            throw new HttpException("Arquivo de traduções não encontrado para o país '{$countryCode}'.");
        }
        $translations = json_decode(file_get_contents($translations), true);
        $html = render($formHtml, [
            'columns' => $columns,
            'eventInfo' => $eventInfo,
            'translations' => $translations,
            'countryCode' => $countryCode,
        ]);

        return $html;
    }
}
