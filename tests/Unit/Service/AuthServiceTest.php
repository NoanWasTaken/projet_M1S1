<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthServiceTest extends TestCase
{
    public function testVerifyCredentialsReturnsUserIfValid(): void
    {
        // Mocks
        
        /** @var EntityManagerInterface&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        /** @var UserPasswordHasherInterface&MockObject $passwordHasherMock */
        $passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);

        /** @var UserRepository&MockObject $userRepositoryMock */
        $userRepositoryMock = $this->createMock(UserRepository::class);

        $email = 'user@example.com';
        $plainPassword = 'password123';
        $user = new User();
        $user->setEmail($email);

        // Le repository doit être appelé avec l'email et retourner utilisateur
        $userRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        // vérif hash pwd
        $passwordHasherMock->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $plainPassword)
            ->willReturn(true);

        // init service        
        $authService = new AuthService(
            $entityManagerMock,
            $passwordHasherMock,
            $userRepositoryMock
        );

        //Executioon
        
        $result = $authService->verifyCredentials($email, $plainPassword);

        $this->assertSame($user, $result);
    }
}
