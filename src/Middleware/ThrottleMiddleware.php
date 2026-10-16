<?php

namespace TeleCore\Middleware;

use TeleCore\Context;

class ThrottleMiddleware implements MiddlewareInterface
{
    protected int $maxAttempts;
    protected int $decaySeconds;

    /**
     * @param int $maxAttempts Maximum number of attempts within a given time frame
     * @param int $decaySeconds Pause duration (in seconds) before the reset count
     */
    public function __construct(int $maxAttempts = 5, int $decaySeconds = 60)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    public function handle(Context $context, callable $next): mixed
    {
        $userId = $context->getUserId();
        
        if (!$userId) {
            return $next($context);
        }

        $cacheKey = "telecore_throttle_{$userId}";
        
        $cacheDir = sys_get_temp_dir() . '/telecore_throttle';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $file = $cacheDir . '/' . md5($cacheKey);
        $currentTime = time();
        $data = ['attempts' => 0, 'expires_at' => $currentTime + $this->decaySeconds];

        if (file_exists($file)) {
            $content = json_decode(file_get_contents($file), true);
            if ($content && $content['expires_at'] > $currentTime) {
                $data = $content;
            }
        }

        if ($data['attempts'] >= $this->maxAttempts) {
            $context->reply("⚠️ <b>Terlalu banyak permintaan!</b> Harap tunggu beberapa saat sebelum mengirim pesan lagi.");
            return null; 
        }

        $data['attempts']++;
        file_put_contents($file, json_encode($data));

        return $next($context);
    }
}