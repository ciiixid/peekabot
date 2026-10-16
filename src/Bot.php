<?php

namespace TeleCore;

use TeleCore\Router\Router;
use TeleCore\Storage\StorageInterface;
use TeleCore\Exceptions\TelegramException;

class Bot
{
    protected string $token;
    protected Request $request;
    protected Router $router;
    protected ?StorageInterface $storage = null;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->request = new Request($token);
        $this->router = new Router($this);
    }

    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;
        return $this;
    }

    public function getStorage(): ?StorageInterface
    {
        return $this->storage;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data) {
                echo "Endpoint Bot TeleCore Aktif dan Berjalan Normal!";
                return;
            }

            $context = new Context($data, $this);
            $this->router->dispatch($context);
            
            http_response_code(200);
            echo "OK";
        } catch (\Throwable $e) {
            http_response_code(500);
            \TeleCore\Logger::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            echo "Internal Error";
        }
    }
}