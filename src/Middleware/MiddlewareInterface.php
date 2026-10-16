<?php

namespace TeleCore\Middleware;

use TeleCore\Context;

interface MiddlewareInterface
{
    /**
     * Handle bot update processing before it reaches the main router/handler.
     * 
     * @param Context $context Telegram update context object
     * @callable $next Closure to forward the request to the next middleware or main handler
     * @return mixed
     */
    public function handle(Context $context, callable $next): mixed;
}