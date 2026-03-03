<?php

namespace App\Tests\Unit\Entity;

use App\Entity\PlayerProfile;
use App\Entity\XPEvent;
use PHPUnit\Framework\TestCase;

class XPEventTest extends TestCase
{
    public function testSetAndGetAmount(): void
    {
        $event = new XPEvent();
        $event->setAmount(100);

        $this->assertSame(100, $event->getAmount());
    }

    public function testSetAndGetReason(): void
    {
        $event = new XPEvent();
        $event->setReason('first_order');

        $this->assertSame('first_order', $event->getReason());
    }

    public function testSetAndGetMeta(): void
    {
        $event = new XPEvent();
        $meta = ['order_id' => 42];
        $event->setMeta($meta);

        $this->assertSame($meta, $event->getMeta());
    }

    public function testSetMetaNull(): void
    {
        $event = new XPEvent();
        $event->setMeta(null);

        $this->assertNull($event->getMeta());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $event = new XPEvent();
        $date = new \DateTimeImmutable('2025-04-01');
        $event->setCreatedAt($date);

        $this->assertSame($date, $event->getCreatedAt());
    }

    public function testSetAndGetProfile(): void
    {
        $event = new XPEvent();
        $profile = new PlayerProfile();

        $event->setProfile($profile);

        $this->assertSame($profile, $event->getProfile());
    }

    public function testSetProfileNull(): void
    {
        $event = new XPEvent();
        $event->setProfile(null);

        $this->assertNull($event->getProfile());
    }

    public function testToString(): void
    {
        $event = new XPEvent();
        $event->setAmount(50);
        $event->setReason('achievement');

        $this->assertSame('+50 XP — achievement', (string) $event);
    }
}
