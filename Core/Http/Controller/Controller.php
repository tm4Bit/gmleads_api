<?php

declare(strict_types=1);

namespace Core\Http\Controller;

use Core\Actions\ActionPayload;
use Core\Exception\HttpException;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

abstract class Controller
{
    protected LoggerInterface $logger;

    protected Request $request;

    protected Response $response;

    protected array $args;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->handle();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws HttpException
     */
    abstract protected function handle(): Response;

    /**
     * @param  array|object|null  $data
     */
    protected function jsonResponse($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function getParams(string $param): string
    {
        if (! isset($this->args[$param])) {
            throw new Exception("Parameter '{$param}' is missing in the request.");
        }

        return $this->args[$param];
    }
}
