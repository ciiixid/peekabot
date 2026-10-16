<?php

namespace TeleCore\Storage;

use Redis;
use RuntimeException;

class RedisStorage implements StorageInterface
{
    protected Redis $redis;

    /**
     * @param array $config Connection configuration ['host' => '127.0.0.1', 'port' => 6379, 'auth' => null, 'database' => 0]
     */
    public function __construct(array $config = [])
    {
        if (!extension_loaded('redis')) {
            throw new RuntimeException("Ekstensi PHP 'redis' belum terpasang di server Anda.");
        }

        $this->redis = new Redis();
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? 0.0;
        $auth = $config['auth'] ?? null;
        $database = $config['database'] ?? 0;

        $this->redis->connect($host, $port, $timeout);

        if ($auth !== null) {
            $this->redis->auth($auth);
        }

        if ($database > 0) {
            $this->redis->select($database);
        }
    }

    public function get(string $key): mixed
    {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return unserialize($data);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $serialized = serialize($value);
        
        if ($ttl > 0) {
            return $this->redis->setex($key, $ttl, $serialized);
        }
        
        return $this->redis->set($key, $serialized);
    }

    public function delete(string $key): bool
    {
        return (bool) $this->redis->del($key);
    }
}