<?php

class User
{
    public $id;
    public $name;
    public $email;
    public $password;
    public $active;
    public $createdAt;

    public static function fromArray(array $data): self
    {
        $obj            = new self();
        $obj->id        = (int) $data['id'];
        $obj->name      = $data['nome'];
        $obj->email     = $data['email'];
        $obj->password  = $data['senha'];
        $obj->active    = (int) ($data['ativo'] ?? 1);
        $obj->createdAt = $data['created_at'] ?? '';
        return $obj;
    }
}
