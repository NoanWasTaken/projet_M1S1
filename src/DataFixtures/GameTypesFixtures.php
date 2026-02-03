<?php

namespace App\DataFixtures;

use App\Entity\GameTypes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameTypesFixtures extends Fixture
{
    private const GAME_TYPES = [
        'RPG',
        'FPS',
        'MOBA',
        'RTS',
        'Action',
        'Adventure',
        'Strategy',
        'Puzzle',
        'Sports',
        'Simulation',
        'Fighting',
        'Racing',
        'Indie',
        'Sandbox',
        'Roguelike',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::GAME_TYPES as $typeName) {
            $existingType = $manager->getRepository(GameTypes::class)->findOneBy([
                'type' => $typeName,
            ]);

            if (!$existingType) {
                $gameType = new GameTypes();
                $gameType->setType($typeName);
                $manager->persist($gameType);
            }
        }

        $manager->flush();
    }
}
