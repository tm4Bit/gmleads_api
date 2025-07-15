<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services\Crm;

use Core\Exception\HttpException;
use Core\Facade\Config;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;

class CrmAuthenticator implements CrmAuthenticatorInterface
{
    public function __construct(
        private HttpClient $httpClient,
        private LoggerInterface $logger
    ) {}

    public function getAuthToken(): string
    {
        $email = Config::get('crm.email');
        $password = Config::get('crm.password');

        if (empty($email) || empty($password)) {
            $this->logger->error('Configuração do CRM não está completa. Verifique as credenciais.');
            throw new HttpException('Configuração do CRM não está completa. Verifique as credenciais.');
        }

        $authResponse = $this->httpClient->post('/api/Token/Auth', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        if ($authResponse->getStatusCode() !== 200) {
            $this->logger->error('Erro ao obter token de autenticação do CRM.', [
                'reason' => $authResponse->getReasonPhrase(),
            ]);
            throw new HttpException('Erro ao obter token de autenticação: '.$authResponse->getReasonPhrase());
        }

        $authBody = json_decode($authResponse->getBody()->getContents(), true);

        if (empty($authBody['token'])) {
            $this->logger->error('Token não encontrado na resposta de autenticação do CRM.');
            throw new HttpException('Token não retornado pelo serviço de autenticação.');
        }

        return $authBody['token'];
    }
}
