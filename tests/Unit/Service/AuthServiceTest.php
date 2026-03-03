<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthServiceTest extends TestCase
{
    public function testVerifyCredentialsReturnsUserIfValid(): void
    {
        $email = 'user@example.com';
        $plainPassword = 'password123';
        $user = new User();
        $user->setEmail($email);

        $em = $this->createStub(EntityManagerInterface::class);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $plainPassword)
            ->willReturn(true);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $authService = new AuthService($em, $passwordHasher, $userRepository);
        $result = $authService->verifyCredentials($email, $plainPassword);

        $this->assertSame($user, $result);
    }

    public function testVerifyCredentialsThrowsWhenUserNotFound(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $authService = new AuthService($em, $passwordHasher, $userRepository);

        $this->expectException(AuthenticationException::class);
        $authService->verifyCredentials('unknown@example.com', 'any');
    }

    public function testVerifyCredentialsThrowsWhenPasswordInvalid(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $em = $this->createStub(EntityManagerInterface::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($user);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->willReturn(false);

        $authService = new AuthService($em, $passwordHasher, $userRepository);

        $this->expectException(AuthenticationException::class);
        $authService->verifyCredentials('user@example.com', 'wrongpassword');
    }

    public function testUpdatePasswordHashesAndFlushes(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $userRepository = $this->createStub(UserRepository::class);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'newpassword')
            ->willReturn('hashed_newpassword');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $authService = new AuthService($em, $passwordHasher, $userRepository);
        $authService->updatePassword($user, 'newpassword');

        $this->assertSame('hashed_newpassword', $user->getPassword());
    }

    public function testGetUserByEmailReturnsUser(): void
    {
        $user = new User();
        $user->setEmail('found@example.com');

        $em = $this->createStub(EntityManagerInterface::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'found@example.com'])
            ->willReturn($user);

        $authService = new AuthService($em, $passwordHasher, $userRepository);
        $result = $authService->getUserByEmail('found@example.com');

        $this->assertSame($user, $result);
    }

    public function testGetUserByEmailReturnsNullWhenNotFound(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'missing@example.com'])
            ->willReturn(null);

        $authService = new AuthService($em, $passwordHasher, $userRepository);
        $result = $authService->getUserByEmail('missing@example.com');

        $this->assertNull($result);
    }
}
