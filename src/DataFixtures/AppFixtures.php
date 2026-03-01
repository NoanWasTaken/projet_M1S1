<?php

namespace App\DataFixtures;

use App\Entity\PlayerProfile;
use App\Entity\Reward;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        $reward = new Reward();
        $reward->setCode('NAVBAR_ORDER_1');
        $reward->setName('Combo Navbar: ordre secret');
        $reward->setType('BADGE');
        $reward->setRuleType('MANUAL');
        $reward->setRuleValue(null);
        $reward->setDescription('Trouver et reproduire l’ordre secret de la navbar.');
        $reward->setUnlocks(['navbarCombo' => true]);
        $reward->setIsActive(true);

        $reward2 = new Reward();
        $reward2->setCode('FOOTER_WORD_ENFANT');
        $reward2->setName('Footer Quest: ENFANT');
        $reward2->setType('COUPON');
        $reward2->setRuleType('WORD_GAME');
        $reward2->setRuleValue('ENFANT');
        $reward2->setDescription('Former le mot ENFANT avec les lettres du footer.');
        $reward2->setUnlocks(['coupon' => true]);
        $reward2->setIsActive(true);
        $reward2->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($reward2);

        if (method_exists($reward, 'setCreatedAt')) {
            $reward->setCreatedAt($now);
        } elseif (method_exists($reward, 'setCretedAt')) {
            $reward->setCretedAt($now);
        }

        $adminPwd   = $_ENV['FIXTURES_ADMIN_PWD']   ?? 'dev-admin';
        $managerPwd = $_ENV['FIXTURES_MANAGER_PWD'] ?? 'dev-manager';
        $clientPwd  = $_ENV['FIXTURES_CLIENT_PWD']  ?? 'dev-client';

        $manager->persist($reward);

        $this->createUserWithProfile(
            $manager,
            email: 'admin@gearforge.test',
            plainPassword: $adminPwd,
            roles: ['ROLE_ADMIN'],
            xpTotal: 1200,
            now: $now
        );

        $this->createUserWithProfile(
            $manager,
            email: 'manager@gearforge.test',
            plainPassword: $managerPwd,
            roles: ['ROLE_MANAGER'],
            xpTotal: 400,
            now: $now
        );

        $this->createUserWithProfile(
            $manager,
            email: 'client@gearforge.test',
            plainPassword: $clientPwd,
            roles: ['ROLE_USER'],
            xpTotal: 0,
            now: $now
        );

        $manager->flush();
    }

    private function createUserWithProfile(
        ObjectManager $manager,
        string $email,
        string $plainPassword,
        array $roles,
        int $xpTotal,
        \DateTimeImmutable $now
    ): void {
        $user = new User();
        $user->setEmail($email);
        [$local] = explode('@', $email);
        $label = ucfirst($local); 

        $user->setName($label);
        $user->setSurname('GearForge');
        $user->setRoles($roles);
        $user->setIsVerified(true);

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $profile = new PlayerProfile();
        // IMPORTANT: ton User a mappedBy "owner" => PlayerProfile doit avoir setOwner()
        $profile->setOwner($user);

        $profile->setXpTotal($xpTotal);
        $profile->setLevel(intdiv($xpTotal, 1000) + 1);

        // timestamps (si tes setters existent)
        if (method_exists($profile, 'setCreatedAt')) {
            $profile->setCreatedAt($now);
        }
        if (method_exists($profile, 'setUpdatedAt')) {
            $profile->setUpdatedAt($now);
        }

        $manager->persist($user);
        $manager->persist($profile);
    }
}