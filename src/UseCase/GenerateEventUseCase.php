<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\UseCase;

use Core\Database;
use Core\Exception\HttpException;
use Core\Exception\HttpNotFoundException;

class GenerateEventUseCase
{
    public function __construct(private Database $db) {}

    public function execute(string $tableName)
    {
        // Verificar se o evento existe na tabela `briefing` e retornar cod_evento e nome_evento
        $eventInfo = $this->db
            ->queryBuilder('SELECT * FROM briefing WHERE tbl_clientes = :tableName', ['tableName' => $tableName])
            ->find();

        if (! $eventInfo) {
            throw new HttpNotFoundException("Evento para a tabela '{$tableName}' não encontrado no briefing.");
        }

        $eventLangId = $eventInfo['pais'];

        $langInfo = $this->db
            ->queryBuilder('SELECT * FROM c_paises WHERE id = :langId', ['langId' => $eventLangId])
            ->find();

        if (! $langInfo) {
            throw new HttpNotFoundException("Idioma não encontrado: {$eventLangId}");
        }

        $lang = config('lang', $langInfo['pais']);
        $country = $lang === 'pt-br' ? 'br' : $lang;

        // Buscar Colunas na tabela do evento
        $columns = $this->db
            ->queryBuilder("SHOW FULL COLUMNS FROM `{$tableName}`")
            ->findAll();

        if (empty($columns)) {
            throw new HttpNotFoundException("A tabela '{$tableName}' não foi encontrada ou não possui colunas.");
        }

        $indexTemplateFile = BASE_PATH.'templates/index.php';
        $formTemplateFile = BASE_PATH.'templates/form.php';

        if (! file_exists($indexTemplateFile) || ! file_exists($formTemplateFile)) {
            throw new HttpException("Arquivos de template não encontrado: {$indexTemplateFile} ou {$formTemplateFile}");
        }

        $eventSlug = str_replace('_', '-', strtolower($tableName));
        $frontendBaseDir = BASE_PATH.'gm_lead';
        $countryDir = $frontendBaseDir.'/'.$country;
        $eventDir = $countryDir.'/'.$eventSlug;
        $indexFilePath = $eventDir.'/index.html';
        $formFilePath = $eventDir.'/form.html';

        $translationsFile = BASE_PATH.'translations/'.$country.'.json';
        if (! file_exists($translationsFile)) {
            throw new HttpException("Arquivo de tradução não encontrado: {$translationsFile}");
        }
        $translations = json_decode(file_get_contents($translationsFile), true);

        $html = render($indexTemplateFile, [
            'lang' => $lang,
            'tableName' => $tableName,
            'eventSlug' => $eventSlug,
            'eventInfo' => $eventInfo,
            'translations' => $translations,
        ]);

        $formHtml = render($formTemplateFile, [
            'lang' => $lang,
            'tableName' => $tableName,
            'eventSlug' => $eventSlug,
            'eventInfo' => $eventInfo,
            'columns' => $columns,
            'translations' => $translations,
        ]);

        // Verifica se o diretório base existe, se não existir, cria
        if (! is_dir($frontendBaseDir)) {
            throw new HttpException("Diretório base não encontrado: {$frontendBaseDir}. Clone o repositório gm_lead no diretório base.");
        }

        // Verifica se o diretório do evento existe, se não existir, cria
        if (! is_dir($eventDir)) {
            // Using PHP's mkdir instead of exec
            shell_exec("mkdir -p {$eventDir}");
            if (! is_dir($eventDir)) {
                throw new HttpException("Falha ao criar diretório do evento: {$eventDir}. Verifique as permissões.");
            }
        }

        // Adiciona os conteúdos HTML aos arquivos
        if (! file_put_contents($indexFilePath, $html)) {
            throw new HttpException("Falha ao salvar arquivo em: {$indexFilePath}");
        }

        if (! file_put_contents($formFilePath, $formHtml)) {
            throw new HttpException("Falha ao salvar arquivo em: {$formFilePath}");
        }

        $gitPushCmd = "cd {$frontendBaseDir} && git add . && git commit -m 'Update event {$tableName}' && git push";
        shell_exec($gitPushCmd.' 2>&1');

        return [
            'message' => 'Evento gerado com sucesso',
            'eventTableName' => $tableName,
            'eventFriendlyName' => $eventInfo['nome_evento'],
            'eventSlug' => $eventSlug,
            'indexFilePath' => $indexFilePath,
            'formFilePath' => $formFilePath,
            'publicUrl' => 'https://leadshowgm.com/'.$country.'/'.$eventSlug,
        ];
    }
}
