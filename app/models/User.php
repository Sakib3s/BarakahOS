<?php

declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    public function findById(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }

        $statement = $this->db->prepare(
            'SELECT id, email, display_name, timezone, is_active, created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, email, password_hash, display_name, timezone, is_active, created_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute([
            'email' => mb_strtolower(trim($email)),
        ]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute([
            'email' => mb_strtolower(trim($email)),
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (email, password_hash, display_name, timezone, is_active)
             VALUES (:email, :password_hash, :display_name, :timezone, :is_active)'
        );

        $statement->execute([
            'email' => mb_strtolower(trim($data['email'])),
            'password_hash' => $data['password_hash'],
            'display_name' => trim($data['display_name']),
            'timezone' => $data['timezone'] ?? 'Asia/Dhaka',
            'is_active' => 1,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
