<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Services;

use Core\Facade\Config;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Core\Exception\HttpException;

class GitHubService
{
    private HttpClient $httpClient;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(HttpClient $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->config = Config::get('github');

        if (empty($this->config['token']) || empty($this->config['owner']) || empty($this->config['repo'])) {
            throw new HttpException('As configurações do GitHub (token, owner, repo) não foram definidas.');
        }
    }

    /**
     * Cria ou atualiza um arquivo no repositório GitHub.
     *
     * @param string $filePath O caminho do arquivo dentro do repositório (ex: "br/nome-do-evento/index.html")
     * @param string $content O conteúdo do arquivo.
     * @param string $commitMessage A mensagem de commit.
     * @return array A resposta da API do GitHub.
     * @throws HttpException Em caso de falha na comunicação com a API.
     */
    public function createOrUpdateFile(string $filePath, string $content, string $commitMessage): array
    {
        $url = sprintf(
            'repos/%s/%s/contents/%s',
            $this->config['owner'],
            $this->config['repo'],
            $filePath
        );

        $fileSha = $this->getFileSha($filePath);

        $payload = [
            'message' => $commitMessage,
            'content' => base64_encode($content),
        ];

        if ($fileSha) {
            $payload['sha'] = $fileSha;
        }

        try {
            $response = $this->httpClient->put($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['token'],
                    'Accept' => 'application/vnd.github.v3+json',
                    'X-GitHub-Api-Version' => '2022-11-28'
                ],
                'json' => $payload,
                'base_uri' => $this->config['base_uri'] // Garante que a base_uri correta seja usada
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logger->error('Falha ao enviar arquivo para o GitHub', [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);
            throw new HttpException('Falha na comunicação com a API do GitHub: ' . $e->getMessage());
        }
    }

    /**
     * Obtém o SHA de um arquivo se ele existir no repositório.
     *
     * @param string $filePath
     * @return string|null O SHA do arquivo ou null se não existir.
     * @throws HttpException
     */
    private function getFileSha(string $filePath): ?string
    {
        $url = sprintf(
            'repos/%s/%s/contents/%s',
            $this->config['owner'],
            $this->config['repo'],
            $filePath
        );

        try {
            $response = $this->httpClient->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['token'],
                    'Accept' => 'application/vnd.github.v3+json',
                ],
                'base_uri' => $this->config['base_uri']
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['sha'] ?? null;
        } catch (ClientException $e) {
            // Um erro 404 é esperado se o arquivo não existe, então o ignoramos.
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            // Outros erros de cliente são inesperados.
            $this->logger->error('Erro ao verificar a existência do arquivo no GitHub', ['error' => $e->getMessage()]);
            throw new HttpException('Erro ao buscar informações do arquivo no GitHub: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            $this->logger->error('Erro genérico do Guzzle ao verificar o arquivo', ['error' => $e->getMessage()]);
            throw new HttpException('Falha na comunicação com o GitHub: ' . $e->getMessage());
        }
    }
}