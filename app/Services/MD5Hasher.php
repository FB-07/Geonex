<?php

namespace App\Services;

use Illuminate\Contracts\Hashing\Hasher;

/**
 * Hasher da tabela `users`. Aceita as passwords antigas em MD5 e grava as novas
 * em bcrypt; AuthController::store() converte as antigas no próximo login.
 */
class MD5Hasher implements Hasher
{
    public function make($value, array $options = [])
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    public function check($value, $hashedValue, array $options = [])
    {
        if ($hashedValue === null || $hashedValue === '') {
            return false;
        }

        if ($this->isBcrypt($hashedValue)) {
            return password_verify($value, $hashedValue);
        }

        return hash_equals($hashedValue, md5($value));
    }

    public function needsRehash($hashedValue, array $options = [])
    {
        return ! $this->isBcrypt($hashedValue);
    }

    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    private function isBcrypt(string $hash): bool
    {
        return str_starts_with($hash, '$2y$');
    }
}
