<?php

namespace TeleCore;

use TeleCore\Exceptions\TelegramException;

class Request
{
    protected string $token;
    protected string $apiUrl = 'https://api.telegram.org/bot';

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function call(string $method, array $parameters = []): mixed
    {
        $url = $this->apiUrl . $this->token . '/' . $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new TelegramException("cURL Error: " . $err);
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['ok']) || $result['ok'] !== true) {
            $description = $result['description'] ?? 'Unknown Telegram Error';
            $errorCode = $result['error_code'] ?? 500;
            throw new TelegramException("Telegram API Error: {$description}", $errorCode);
        }

        return $result['result'];
    }
}