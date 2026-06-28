<?php

namespace App\Services;

use Exception;

class SankhyaClient
{
    private array $api;
    private array $app;
    private ?string $bearerToken = null;

    public function __construct(array $api, array $app)
    {
        $this->api = $api;
        $this->app = $app;
    }

    public function isSandbox(): bool
    {
        return (bool)$this->app['sandbox'];
    }

    public function login(): string
    {
        if ($this->isSandbox()) {
            app_log('Login simulado');
            return 'SANDBOX_TOKEN';
        }

        $url = rtrim($this->api['base_url'], '/') . '/login';

        $headers = [
            'token: ' . $this->api['token'],
            'appkey: ' . $this->api['appkey'],
            'Content-Type: application/json'
        ];

        $response = $this->http('POST', $url, [], $headers);
        $token = $response['bearerToken'] ?? $response['token'] ?? null;

        if (!$token) {
            throw new Exception('Login não retornou bearerToken/token.');
        }

        $this->bearerToken = $token;
        return $token;
    }

    public function service(string $serviceName, array $requestBody): array
    {
        if ($this->isSandbox()) {
            app_log('Serviço simulado: ' . $serviceName, $requestBody);
            return [
                'status' => '1',
                'responseBody' => [
                    'simulado' => true,
                    'nunota' => random_int(700000, 999999),
                    'codparc' => 12345,
                    'codprod' => random_int(1000, 9999),
                ]
            ];
        }

        if (!$this->bearerToken) {
            $this->login();
        }

        $url = rtrim($this->api['base_url'], '/') .
            '/gateway/v1/mge/service.sbr?serviceName=' . urlencode($serviceName) .
            '&outputType=json';

        return $this->http('POST', $url, [
            'serviceName' => $serviceName,
            'requestBody' => $requestBody
        ], [
            'Authorization: Bearer ' . $this->bearerToken,
            'Content-Type: application/json'
        ]);
    }

    private function http(string $method, string $url, array $payload, array $headers): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($payload) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        app_log('HTTP ' . $method . ' ' . $url, [
            'http_code' => $code,
            'error' => $err,
            'response' => $raw,
        ]);

        if ($err) {
            throw new Exception('Erro HTTP: ' . $err);
        }

        $json = json_decode((string)$raw, true);

        if (!is_array($json)) {
            throw new Exception('Resposta inválida da API: ' . $raw);
        }

        return $json;
    }
}
