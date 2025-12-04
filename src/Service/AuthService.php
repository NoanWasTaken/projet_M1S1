<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    ) {
    }

    /**
     * Register a new user
     */
    public function register(string $email, string $plainPassword): User
    {
        // Check if user already exists
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \RuntimeException('Un utilisateur avec cet email existe déjà.');
        }

        $user = new User();
        $user->setEmail($email);
        
        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        
        // Set default role
        $user->setRoles(['ROLE_USER']);

        // Persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $email, string $plainPassword): ?User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new AuthenticationException('Email ou mot de passe incorrect.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $plainPassword)) {
            throw new AuthenticationException('Email ou mot de passe incorrect.');
        }

        return $user;
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, string $plainPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
