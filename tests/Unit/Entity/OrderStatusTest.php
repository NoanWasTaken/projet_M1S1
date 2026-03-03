<?php

namespace App\Tests\Unit\Entity;

use App\Enum\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function testPendingValue(): void
    {
        $this->assertSame('pending', OrderStatus::PENDING->value);
    }

    public function testValidatedValue(): void
    {
        $this->assertSame('validated', OrderStatus::VALIDATED->value);
    }

    public function testCancelledValue(): void
    {
        $this->assertSame('cancelled', OrderStatus::CANCELLED->value);
    }

    public function testPendingLabel(): void
    {
        $this->assertSame('En attente', OrderStatus::PENDING->label());
    }

    public function testValidatedLabel(): void
    {
        $this->assertSame('Validée', OrderStatus::VALIDATED->label());
    }

    public function testCancelledLabel(): void
    {
        $this->assertSame('Annulée', OrderStatus::CANCELLED->label());
    }

    public function testFromString(): void
    {
        $status = OrderStatus::from('pending');

        $this->assertSame(OrderStatus::PENDING, $status);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $status = OrderStatus::tryFrom('unknown');

        $this->assertNull($status);
    }
}
