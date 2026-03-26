<?php

class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;

    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function isBlocked(string $ip): bool
    {
        $this->purgeOld($ip);

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE ip = :ip AND created_at >= DATE_SUB(NOW(), INTERVAL :w SECOND)'
        );
        $stmt->execute([':ip' => $ip, ':w' => self::WINDOW_SECONDS]);

        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public function record(string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (ip) VALUES (:ip)'
        );
        $stmt->execute([':ip' => $ip]);
    }

    public function clear(string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM login_attempts WHERE ip = :ip'
        );
        $stmt->execute([':ip' => $ip]);
    }

    private function purgeOld(string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM login_attempts
             WHERE ip = :ip AND created_at < DATE_SUB(NOW(), INTERVAL :w SECOND)'
        );
        $stmt->execute([':ip' => $ip, ':w' => self::WINDOW_SECONDS]);
    }
}
