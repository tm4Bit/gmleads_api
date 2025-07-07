<?php

declare(strict_types=1);

namespace Http\Controller;

use Core\App;
use Core\Database;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class HealthController
{
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $db = App::resolve(Database::class);
        $logger = App::resolve(LoggerInterface::class);
        try {
            $fuso = $db->queryBuilder('SELECT fuso FROM c_paises WHERE id = 1')->find();
        } catch (\Exception $e) {
            $logger->error('Erro ao conectar ao banco de dados.', ['error' => $e->getMessage()]);
            $payload = json_encode([
                'status' => 'OFF',
                'message' => 'Serviço inativo',
            ]);
            $response->getBody()->write($payload);

            return $response->withStatus(503);
        }

        $logger->info('Verificação de saúde bem-sucedida.', [$fuso]);
        $payload = json_encode([
            'status' => 'ON',
            'message' => 'Serviço ativo',
        ]);
        $response->getBody()->write($payload);

        return $response;
    }
}
