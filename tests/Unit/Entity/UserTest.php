<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserEmailGetterAndSetter(): void
    {
        //init
        $user = new User();
        $email = 'test@example.com';

        //Action
        $user->setEmail($email);

        // Assert
        $this->assertSame($email, $user->getEmail());
    }
}
