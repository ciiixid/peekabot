<?php

namespace TeleCore\Router;

use TeleCore\Context;

class Route
{
    protected string $type; // 'command', 'text', 'regex', 'callback'
    protected string $pattern;
    /** @var callable */
    protected $handler;
    protected array $middleware = [];

    public function __construct(string $type, string $pattern, callable $handler)
    {
        $this->type = $type;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public function middleware(array|string $middleware): self
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middlewares);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function matches(Context $context): bool
    {
        $text = $context->getText();
        if ($text === null) {
            return false;
        }

        switch ($this->type) {
            case 'command':
                // Command format: /start, /help (case-insensitive; the / prefix is ​​optional)
                $cleanCommand = ltrim($this->pattern, '/');
                $userText = trim($text);
                return strcasecmp($userText, '/' . $cleanCommand) === 0 || 
                       stripos($userText, '/' . $cleanCommand . ' ') === 0;

            case 'text':
                return strcasecmp(trim($text), trim($this->pattern)) === 0;

            case 'regex':
                return (bool) preg_match($this->pattern, $text);

            case 'callback':
                $update = $context->getUpdate();
                if (isset($update['callback_query'])) {
                    $data = $update['callback_query']['data'] ?? '';
                    return strcasecmp(trim($data), trim($this->pattern)) === 0;
                }
                return false;

            default:
                return false;
        }
    }

    public function execute(Context $context): mixed
    {
        $handler = $this->handler;
        return $handler($context);
    }
}