<?php

namespace TeleCore\Storage;

use PDO;

class DatabaseStorage implements StorageInterface
{
    protected PDO $pdo;
    protected string $table;

    /**
     * @param PDO $pdo PDO connection active
     * @param string $table Table name for storing bot sessions
     */
    public function __construct(PDO $pdo, string$table = 'telecore_storage')
    {
        $this->pdo =$pdo;
        $this->table =$table;
        $this->initTable();
    }

    /**
     * Automatically create a table if one does not yet exist
     */
    protected function initTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `s_key` VARCHAR(255) PRIMARY KEY,
            `s_value` LONGTEXT NOT NULL,
            `expires_at` BIGINT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->pdo->exec($sql);
    }

    public function get(string $key): mixed
    {
        $this->gc();

        $stmt =$this->pdo->prepare("SELECT s_value, expires_at FROM `{$this->table}` WHERE `s_key` = ?");
        $stmt->execute([$key]);
        $row =$stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        if ($row['expires_at'] > 0 && time() >$row['expires_at']) {
            $this->delete($key);
            return null;
        }

        return unserialize($row['s_value']);
    }

    public function set(string $key, mixed $value, int$ttl = 0): bool
    {
        $serialized = serialize($value);$expiresAt = $ttl > 0 ? time() + $ttl : 0;

        $stmt =$this->pdo->prepare("
            INSERT INTO `{$this->table}` (`s_key`, `s_value`, `expires_at`) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE `s_value` = VALUES(`s_value`), `expires_at` = VALUES(`expires_at`)
        ");

        return $stmt->execute([$key, $serialized,$expiresAt]);
    }

    public function delete(string $key): bool
    {
        $stmt =$this->pdo->prepare("DELETE FROM `{$this->table}` WHERE `s_key` = ?");
        return $stmt->execute([$key]);
    }

    /**
     * Garbage Collection: Probabilistically deleting expired data (1% chance per set/get operation)
     */
    protected function gc(): void
    {
        if (mt_rand(1, 100) === 1) {
            $stmt =$this->pdo->prepare("DELETE FROM `{$this->table}` WHERE `expires_at` > 0 AND `expires_at` < ?");
            $stmt->execute([time()]);
        }
    }
}