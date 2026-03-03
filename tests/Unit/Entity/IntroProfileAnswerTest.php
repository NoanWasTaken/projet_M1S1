<?php

namespace App\Tests\Unit\Entity;

use App\Entity\IntroProfileAnswer;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class IntroProfileAnswerTest extends TestCase
{
    public function testAnsweredAtIsSetOnConstruct(): void
    {
        $answer = new IntroProfileAnswer();

        $this->assertNotNull($answer->getAnsweredAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $answer->getAnsweredAt());
    }

    public function testIdIsNullByDefault(): void
    {
        $answer = new IntroProfileAnswer();

        $this->assertNull($answer->getId());
    }

    public function testSetAndGetUser(): void
    {
        $answer = new IntroProfileAnswer();
        $user = new User();
        $user->setEmail('gamer@example.com');

        $answer->setUser($user);

        $this->assertSame($user, $answer->getUser());
    }

    public function testSetUserNull(): void
    {
        $answer = new IntroProfileAnswer();
        $answer->setUser(null);

        $this->assertNull($answer->getUser());
    }

    public function testSetAndGetGameType(): void
    {
        $answer = new IntroProfileAnswer();
        $answer->setGameType('FPS');

        $this->assertSame('FPS', $answer->getGameType());
    }
}
