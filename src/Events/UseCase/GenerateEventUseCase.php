<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\UseCase;

use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;
use Ovlk\GMLeads\Events\Repository\EventRepositoryInterface;
use Ovlk\GMLeads\Services\GitHubServiceInterface;

class GenerateEventUseCase
{

    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private GitHubServiceInterface $githubService
    ) {}

    public function execute(string $tableName)
    {
        // Etapa 1: Buscar Dados do Evento (sem alteração)
        $eventInfo = $this->eventRepository->findByTableName($tableName);

        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$tableName}' não encontrado no briefing.");
        }

        // Determinar o diretório do país
        $countryCode = strtolower(explode('.', $eventInfo['arquivo_pais'])[0]); // ex: "br.json" -> "br"
        
        // Etapa 2: Buscar Colunas e Template
        $columns = $this->eventRepository->getTableColumns($tableName);

        if (empty($columns)) {
            throw new HttpNotFoundException("A tabela '{$tableName}' não foi encontrada ou não possui colunas.");
        }

        $templateTypeInfo = $this->eventRepository->getHtmlTemplate($eventInfo['tipo_evento']);
            
        $htmlTemplate = $templateTypeInfo['html'] ?? file_get_contents(BASE_PATH.'templates/default_template.html');

        if (empty($htmlTemplate)) {
            throw new HttpException("Nenhum template HTML encontrado para este tipo de evento e nenhum template padrão definido.");
        }

        // Etapa 3 e 4: Gerar o HTML Final
        $eventSlug = normalize_string($eventInfo['nome_evento']);
        $formHtml = $this->generateFormHtml($columns);
        
        // Substituir os placeholders no template
        $finalHtml = str_replace(
            ['{% banner %}', '{% logo %}', '{% form %}'],
            [$eventInfo['bg_desk'], 'URL_DO_LOGO_PADRAO', $formHtml],
            $htmlTemplate
        );

        // Etapa 5: Deploy no GitHub usando o serviço
        $filePathInRepo = sprintf('%s/%s/index.html', $countryCode, $eventSlug);
        $commitMessage = sprintf('feat: Publica ou atualiza o evento %s', $eventInfo['nome_evento']);

        $this->githubService->createOrUpdateFile($filePathInRepo, $finalHtml, $commitMessage);
        
        // Montar a URL pública final
        $publicUrl = sprintf('https://leadshowgm.com/%s/%s', $countryCode, $eventSlug);
        
        // Atualizar a URL no banco de dados
        // $this->db->queryBuilder('UPDATE briefing SET url = :url WHERE id = :id', [
        //     'url' => $publicUrl,
        //     'id' => $eventInfo['id']
        // ]);

        return [
            "success" => true,
            "message" => "Evento publicado com sucesso no GitHub.",
            "event_url" => $publicUrl,
            "table_name" => $tableName,
            "country" => $countryCode
        ];
    }
    
    /**
     * Função auxiliar para gerar o HTML do formulário a partir das colunas.
     * (Esta lógica pode ser extraída para uma classe separada se ficar muito complexa)
     */
    private function generateFormHtml(array $columns): string
    {
        $formFields = '';
        foreach ($columns as $column) {
            // Ignorar colunas que não devem virar campos de formulário
            if (in_array($column['Field'], ['id', 'mom', 'k'])) {
                continue;
            }
            
            $label = htmlspecialchars(ucfirst(str_replace('_', ' ', $column['Field'])));
            $name = htmlspecialchars($column['Field']);
            
            // Lógica simples para determinar o tipo de input
            $type = 'text';
            if (str_contains(strtolower($column['Type']), 'date')) {
                $type = 'date';
            } elseif ($name === 'email') {
                $type = 'email';
            } elseif ($name === 'cel') {
                $type = 'tel';
            }

            $formFields .= "<div>\n";
            $formFields .= "  <label for=\"{$name}\">{$label}:</label>\n";
            $formFields .= "  <input type=\"{$type}\" id=\"{$name}\" name=\"{$name}\" required>\n";
            $formFields .= "</div>\n\n";
        }

        return "<form id=\"lead-form\">\n{$formFields}<button type=\"submit\">Enviar</button>\n</form>";
    }
}