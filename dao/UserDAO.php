<?php

class UserDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
