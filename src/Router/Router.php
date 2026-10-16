<?php

namespace TeleCore\Router;

use TeleCore\Bot;
use TeleCore\Context;
use TeleCore\Middleware\MiddlewareInterface;

class Router
{
    protected Bot $bot;
    /** @var Route[] */
    protected array $routes = [];
    protected array $globalMiddleware = [];
    /** @var callable|null */
    protected $fallbackHandler = null;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    public function addGlobalMiddleware(string|MiddlewareInterface $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    public function command(string $command, callable $handler): Route
    {
        $route = new Route('command', $command, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function hears(string $text, callable $handler): Route
    {
        $route = new Route('text', $text, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function regex(string $pattern, callable $handler): Route
    {
        $route = new Route('regex', $pattern, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function callback(string $data, callable $handler): Route
    {
        $route = new Route('callback', $data, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function fallback(callable $handler): self
    {
        $this->fallbackHandler = $handler;
        return $this;
    }

    public function dispatch(Context $context): mixed
    {
        $matchedRoute = null;

        foreach ($this->routes as $route) {
            if ($route->matches($context)) {
                $matchedRoute = $route;
                break;
            }
        }

        if (!$matchedRoute) {
            if ($this->fallbackHandler) {
                $fallback = $this->fallbackHandler;
                return $fallback($context);
            }
            return null;
        }

        // Combine global middleware and route middleware
        $middlewares = array_merge($this->globalMiddleware, $matchedRoute->getMiddleware());

        // Execute the middleware pipeline using the onion/closure delegation pattern
        $coreHandler = function ($ctx) use ($matchedRoute) {
            return $matchedRoute->execute($ctx);
        };

        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function ($ctx) use ($next, $middleware) {
                    if (is_string($middleware) && class_exists($middleware)) {
                        $instance = new $middleware();
                        if ($instance instanceof MiddlewareInterface) {
                            return $instance->handle($ctx, $next);
                        }
                    } elseif ($middleware instanceof MiddlewareInterface) {
                        return $middleware->handle($ctx, $next);
                    }
                    return $next($ctx);
                };
            },
            $coreHandler
        );

        return $pipeline($context);
    }
}