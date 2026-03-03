<?php

namespace App\Tests\Unit\Entity;

use App\Entity\PlayerProfile;
use App\Entity\User;
use App\Entity\UserReward;
use App\Entity\XPEvent;
use PHPUnit\Framework\TestCase;

class PlayerProfileTest extends TestCase
{
    public function testDefaults(): void
    {
        $profile = new PlayerProfile();

        $this->assertSame(0, $profile->getXpTotal());
        $this->assertSame(0, $profile->getLevel());
        $this->assertSame('bald_head.webp', $profile->getHairSkin());
        $this->assertSame('normal_body.webp', $profile->getBodySkin());
        $this->assertCount(0, $profile->getXPEvents());
        $this->assertCount(0, $profile->getUserRewards());
    }

    public function testSetAndGetOwner(): void
    {
        $profile = new PlayerProfile();
        $user = new User();
        $user->setEmail('player@example.com');

        $profile->setOwner($user);

        $this->assertSame($user, $profile->getOwner());
    }

    public function testSetAndGetXpTotal(): void
    {
        $profile = new PlayerProfile();
        $profile->setXpTotal(500);

        $this->assertSame(500, $profile->getXpTotal());
    }

    public function testSetAndGetLevel(): void
    {
        $profile = new PlayerProfile();
        $profile->setLevel(3);

        $this->assertSame(3, $profile->getLevel());
    }

    public function testSetAndGetHairSkin(): void
    {
        $profile = new PlayerProfile();
        $profile->setHairSkin('custom_hair.webp');

        $this->assertSame('custom_hair.webp', $profile->getHairSkin());
    }

    public function testSetAndGetBodySkin(): void
    {
        $profile = new PlayerProfile();
        $profile->setBodySkin('custom_body.webp');

        $this->assertSame('custom_body.webp', $profile->getBodySkin());
    }

    public function testAddXPEvent(): void
    {
        $profile = new PlayerProfile();
        $event = new XPEvent();

        $profile->addXPEvent($event);

        $this->assertCount(1, $profile->getXPEvents());
        $this->assertSame($profile, $event->getProfile());
    }

    public function testAddXPEventNoDuplicate(): void
    {
        $profile = new PlayerProfile();
        $event = new XPEvent();

        $profile->addXPEvent($event);
        $profile->addXPEvent($event);

        $this->assertCount(1, $profile->getXPEvents());
    }

    public function testRemoveXPEvent(): void
    {
        $profile = new PlayerProfile();
        $event = new XPEvent();

        $profile->addXPEvent($event);
        $profile->removeXPEvent($event);

        $this->assertCount(0, $profile->getXPEvents());
    }

    public function testAddUserReward(): void
    {
        $profile = new PlayerProfile();
        $userReward = new UserReward();

        $profile->addUserReward($userReward);

        $this->assertCount(1, $profile->getUserRewards());
        $this->assertSame($profile, $userReward->getProfile());
    }

    public function testAddUserRewardNoDuplicate(): void
    {
        $profile = new PlayerProfile();
        $userReward = new UserReward();

        $profile->addUserReward($userReward);
        $profile->addUserReward($userReward);

        $this->assertCount(1, $profile->getUserRewards());
    }

    public function testRemoveUserReward(): void
    {
        $profile = new PlayerProfile();
        $userReward = new UserReward();

        $profile->addUserReward($userReward);
        $profile->removeUserReward($userReward);

        $this->assertCount(0, $profile->getUserRewards());
    }

    public function testSetCreatedAt(): void
    {
        $profile = new PlayerProfile();
        $date = new \DateTimeImmutable('2025-02-01');
        $profile->setCreatedAt($date);

        $this->assertSame($date, $profile->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $profile = new PlayerProfile();
        $date = new \DateTimeImmutable('2025-02-02');
        $profile->setUpdatedAt($date);

        $this->assertSame($date, $profile->getUpdatedAt());
    }
}
