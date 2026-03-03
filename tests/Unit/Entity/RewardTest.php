<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Reward;
use App\Entity\UserReward;
use PHPUnit\Framework\TestCase;

class RewardTest extends TestCase
{
    public function testCreatedAtIsSetOnConstruct(): void
    {
        $reward = new Reward();

        $this->assertNotNull($reward->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $reward->getCreatedAt());
    }

    public function testInitialUserRewardsEmpty(): void
    {
        $reward = new Reward();

        $this->assertCount(0, $reward->getUserRewards());
    }

    public function testSetAndGetCode(): void
    {
        $reward = new Reward();
        $reward->setCode('BADGE_XP_100');

        $this->assertSame('BADGE_XP_100', $reward->getCode());
    }

    public function testSetAndGetName(): void
    {
        $reward = new Reward();
        $reward->setName('Badge XP 100');

        $this->assertSame('Badge XP 100', $reward->getName());
    }

    public function testSetAndGetType(): void
    {
        $reward = new Reward();
        $reward->setType('badge');

        $this->assertSame('badge', $reward->getType());
    }

    public function testSetAndGetRuletype(): void
    {
        $reward = new Reward();
        $reward->setRuletype('xp_threshold');

        $this->assertSame('xp_threshold', $reward->getRuletype());
    }

    public function testSetAndGetRuleValue(): void
    {
        $reward = new Reward();
        $reward->setRuleValue('100');

        $this->assertSame('100', $reward->getRuleValue());
    }

    public function testSetRuleValueNull(): void
    {
        $reward = new Reward();
        $reward->setRuleValue(null);

        $this->assertNull($reward->getRuleValue());
    }

    public function testSetAndGetDescription(): void
    {
        $reward = new Reward();
        $reward->setDescription('Atteindre 100 XP');

        $this->assertSame('Atteindre 100 XP', $reward->getDescription());
    }

    public function testSetAndGetUnlocks(): void
    {
        $reward = new Reward();
        $unlocks = ['skin_1', 'skin_2'];
        $reward->setUnlocks($unlocks);

        $this->assertSame($unlocks, $reward->getUnlocks());
    }

    public function testSetUnlocksNull(): void
    {
        $reward = new Reward();
        $reward->setUnlocks(null);

        $this->assertNull($reward->getUnlocks());
    }

    public function testSetAndGetIsActive(): void
    {
        $reward = new Reward();
        $reward->setIsActive(true);

        $this->assertTrue($reward->isActive());
    }

    public function testSetIsActiveFalse(): void
    {
        $reward = new Reward();
        $reward->setIsActive(false);

        $this->assertFalse($reward->isActive());
    }

    public function testSetCreatedAt(): void
    {
        $reward = new Reward();
        $date = new \DateTimeImmutable('2025-01-01');
        $reward->setCreatedAt($date);

        $this->assertSame($date, $reward->getCreatedAt());
    }

    public function testAddUserReward(): void
    {
        $reward = new Reward();
        $userReward = new UserReward();

        $reward->addUserReward($userReward);

        $this->assertCount(1, $reward->getUserRewards());
        $this->assertSame($reward, $userReward->getReward());
    }

    public function testAddUserRewardNoDuplicate(): void
    {
        $reward = new Reward();
        $userReward = new UserReward();

        $reward->addUserReward($userReward);
        $reward->addUserReward($userReward);

        $this->assertCount(1, $reward->getUserRewards());
    }

    public function testRemoveUserReward(): void
    {
        $reward = new Reward();
        $userReward = new UserReward();

        $reward->addUserReward($userReward);
        $reward->removeUserReward($userReward);

        $this->assertCount(0, $reward->getUserRewards());
    }

    public function testToString(): void
    {
        $reward = new Reward();
        $reward->setCode('CODE1');
        $reward->setName('Mon Badge');

        $this->assertSame('Mon Badge', (string) $reward);
    }
}
