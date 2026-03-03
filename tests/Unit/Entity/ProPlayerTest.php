<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ProPlayer;
use PHPUnit\Framework\TestCase;

class ProPlayerTest extends TestCase
{
    public function testIdIsNullByDefault(): void
    {
        $player = new ProPlayer();

        $this->assertNull($player->getId());
    }

    public function testSetAndGetName(): void
    {
        $player = new ProPlayer();
        $player->setName('s1mple');

        $this->assertSame('s1mple', $player->getName());
    }

    public function testSetAndGetRole(): void
    {
        $player = new ProPlayer();
        $player->setRole('Sniper');

        $this->assertSame('Sniper', $player->getRole());
    }

    public function testSetAndGetTeam(): void
    {
        $player = new ProPlayer();
        $player->setTeam('NaVi');

        $this->assertSame('NaVi', $player->getTeam());
    }

    public function testSetAndGetCountry(): void
    {
        $player = new ProPlayer();
        $player->setCountry('Ukraine');

        $this->assertSame('Ukraine', $player->getCountry());
    }

    public function testSetAndGetPhoto(): void
    {
        $player = new ProPlayer();
        $player->setPhoto('s1mple.jpg');

        $this->assertSame('s1mple.jpg', $player->getPhoto());
    }

    public function testSetPhotoNull(): void
    {
        $player = new ProPlayer();
        $player->setPhoto(null);

        $this->assertNull($player->getPhoto());
    }

    public function testSetAndGetGame(): void
    {
        $player = new ProPlayer();
        $player->setGame('CS2');

        $this->assertSame('CS2', $player->getGame());
    }

    public function testSetAndGetMouse(): void
    {
        $player = new ProPlayer();
        $player->setMouse('Zowie EC2');

        $this->assertSame('Zowie EC2', $player->getMouse());
    }

    public function testSetAndGetKeyboard(): void
    {
        $player = new ProPlayer();
        $player->setKeyboard('HyperX Alloy');

        $this->assertSame('HyperX Alloy', $player->getKeyboard());
    }

    public function testSetAndGetHeadset(): void
    {
        $player = new ProPlayer();
        $player->setHeadset('SteelSeries Arctis 7');

        $this->assertSame('SteelSeries Arctis 7', $player->getHeadset());
    }

    public function testSetAndGetDescription(): void
    {
        $player = new ProPlayer();
        $player->setDescription('Meilleur joueur CS du monde');

        $this->assertSame('Meilleur joueur CS du monde', $player->getDescription());
    }

    public function testSetDescriptionNull(): void
    {
        $player = new ProPlayer();
        $player->setDescription(null);

        $this->assertNull($player->getDescription());
    }

    public function testToString(): void
    {
        $player = new ProPlayer();
        $player->setName('ZywOo');

        $this->assertSame('ZywOo', (string) $player);
    }
}
