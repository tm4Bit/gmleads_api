<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Services;

use Core\Exception\HttpException;

/**
 * Interface para serviços que interagem com repositórios Git via API.
 * Define um contrato para criar ou atualizar arquivos.
 */
interface GithubServiceInterface
{
    /**
     * Cria ou atualiza um arquivo em um repositório.
     *
     * @param  string  $filePath  O caminho do arquivo dentro do repositório (ex: "br/nome-do-evento/index.html").
     * @param  string  $content  O conteúdo do arquivo a ser salvo.
     * @param  string  $commitMessage  A mensagem de commit para o versionamento.
     * @return array A resposta da API do provedor Git.
     *
     * @throws HttpException Em caso de falha na comunicação ou erro na API.
     */
    public function createOrUpdateFile(string $filePath, string $content, string $commitMessage): array;
}
