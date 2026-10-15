<?php

namespace Tests\Unit;

use App\Services\MD5Hasher;
use PHPUnit\Framework\TestCase;

class PasswordHasherTest extends TestCase
{
    public function test_new_passwords_are_bcrypt_and_verify(): void
    {
        $hasher = new MD5Hasher();
        $hash = $hasher->make('Admin123!');

        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertLessThanOrEqual(64, strlen($hash));
        $this->assertTrue($hasher->check('Admin123!', $hash));
        $this->assertFalse($hasher->check('outra', $hash));
        $this->assertFalse($hasher->needsRehash($hash));
    }

    public function test_legacy_md5_passwords_still_work_and_need_rehash(): void
    {
        $hasher = new MD5Hasher();
        $legacy = md5('Admin123!');

        $this->assertTrue($hasher->check('Admin123!', $legacy));
        $this->assertFalse($hasher->check('outra', $legacy));
        $this->assertTrue($hasher->needsRehash($legacy));
    }

    public function test_empty_hash_never_matches(): void
    {
        $this->assertFalse((new MD5Hasher())->check('', ''));
        $this->assertFalse((new MD5Hasher())->check('x', null));
    }
}
