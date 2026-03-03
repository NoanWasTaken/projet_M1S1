<?php

namespace App\Tests\Unit\Entity;

use App\Entity\PlayerProfile;
use App\Entity\Reward;
use App\Entity\UserReward;
use PHPUnit\Framework\TestCase;

class UserRewardTest extends TestCase
{
    public function testSetAndGetUnlockedAt(): void
    {
        $userReward = new UserReward();
        $date = new \DateTimeImmutable('2025-03-15');
        $userReward->setUnlockedAt($date);

        $this->assertSame($date, $userReward->getUnlockedAt());
    }

    public function testSetAndGetSource(): void
    {
        $userReward = new UserReward();
        $userReward->setSource('purchase');

        $this->assertSame('purchase', $userReward->getSource());
    }

    public function testSetSourceNull(): void
    {
        $userReward = new UserReward();
        $userReward->setSource(null);

        $this->assertNull($userReward->getSource());
    }

    public function testSetAndGetMeta(): void
    {
        $userReward = new UserReward();
        $meta = ['level' => 5];
        $userReward->setMeta($meta);

        $this->assertSame($meta, $userReward->getMeta());
    }

    public function testSetMetaNull(): void
    {
        $userReward = new UserReward();
        $userReward->setMeta(null);

        $this->assertNull($userReward->getMeta());
    }

    public function testSetAndGetProfile(): void
    {
        $userReward = new UserReward();
        $profile = new PlayerProfile();

        $userReward->setProfile($profile);

        $this->assertSame($profile, $userReward->getProfile());
    }

    public function testSetAndGetReward(): void
    {
        $userReward = new UserReward();
        $reward = new Reward();
        $reward->setCode('FIRST_WIN');
        $reward->setName('Première victoire');
        $reward->setType('badge');
        $reward->setRuletype('static');
        $reward->setIsActive(true);

        $userReward->setReward($reward);

        $this->assertSame($reward, $userReward->getReward());
    }
}
