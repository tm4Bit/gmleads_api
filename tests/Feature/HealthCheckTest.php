<?php

test('health check endpoint returns a successful response', function () {
    $request = $this->createRequest('GET', '/api/up');

    $response = $this->app->handle($request);

    $payload = (string) $response->getBody();
    $responseDecoded = json_decode($payload, true);
    $data = $responseDecoded['data'];

    expect($response->getStatusCode())->toBe(200);
    expect($data)->toHaveKeys(['status', 'message', 'timezone']);
    expect($data['status'])->toBe('ON');
    expect($data['message'])->toBe('Serviço ativo');
});
