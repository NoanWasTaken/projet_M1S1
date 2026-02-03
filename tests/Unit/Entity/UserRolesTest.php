<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserRolesTest extends TestCase
{
    public function testGetRolesIncludesDefaultRole(): void
    {
        // Initialisation
        $user = new User();

        // Action
        $roles = $user->getRoles();

        // Assertion
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(1, $roles);
    }

    public function testGetRolesEnsuresUniqueness(): void
    {
        // Initialisation
        $user = new User();
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        // Action
        $roles = $user->getRoles();

        // Assertion
        $this->assertCount(2, $roles); // ROLE_ADMIN + ROLE_USER
        $this->assertContains('ROLE_ADMIN', $roles);
    }
}
