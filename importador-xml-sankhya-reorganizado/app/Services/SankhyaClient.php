<?php

namespace App\Services;

use Exception;

class SankhyaClient
{
    private array $config;
    private ?string $bearerToken = null;

    public function __construct()
    {
        $this->config = config('sankhya', []);
    }

    public function login(): string
    {
        if (config('app.sandbox')) {
            log_app('Login Sankhya ignorado em modo sandbox.');
            return 'SANDBOX_TOKEN';
        }

        $url = $this->config['base_url'] . '/login';

        $headers = [
            'Content-Type: application/json',
        ];

        if (!empty($this->config['token'])) {
            $headers[] = 'token: ' . $this->config['token'];
        }

        if (!empty($this->config['appkey'])) {
            $headers[] = 'appkey: ' . $this->config['appkey'];
        }

        $payload = [];
        if (!empty($this->config['username']) && !empty($this->config['password'])) {
            $payload = [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ];
        }

        $response = $this->request('POST', $url, $payload, $headers);

        $token = $response['bearerToken'] ?? $response['token'] ?? null;
        if (!$token) {
            throw new Exception('Login Sankhya não retornou token.');
        }

        $this->bearerToken = $token;

        return $token;
    }

    public function buscarParceiroPorDocumento(string $documento): array
    {
        if (config('app.sandbox')) {
            log_app('Consulta de parceiro simulada.', ['documento' => $documento]);
            return [
                'encontrado' => false,
                'documento' => $documento,
                'mensagem' => 'Parceiro não consultado porque o app está em modo sandbox.',
            ];
        }

        throw new Exception('Implementar consulta ao parceiro conforme endpoint Sankhya disponível no ambiente.');
    }

    public function enviarPedido(array $payload): array
    {
        if (config('app.sandbox')) {
            log_app('Pedido simulado.', ['payload' => $payload]);
            return [
                'sandbox' => true,
                'message' => 'Pedido não enviado. Modo sandbox ativo.',
                'payload' => $payload,
            ];
        }

        throw new Exception('Implementar envio de pedido conforme endpoint Sankhya disponível no ambiente.');
    }

    private function request(string $method, string $url, array $payload = [], array $headers = []): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
        ]);

        if ($payload) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Erro cURL: ' . $error);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($body, true);

        if ($status >= 400) {
            throw new Exception('Erro HTTP Sankhya ' . $status . ': ' . $body);
        }

        return is_array($decoded) ? $decoded : ['raw' => $body];
    }
}
