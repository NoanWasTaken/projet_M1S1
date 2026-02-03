<?php

namespace App\Fixtures;

use App\Entity\GameTypes;
use Doctrine\ORM\EntityManagerInterface;

class GameTypesFixtures
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

    public static function load(EntityManagerInterface $entityManager): void
    {
        foreach (self::GAME_TYPES as $typeName) {
            // Check if game type already exists
            $existingType = $entityManager->getRepository(GameTypes::class)->findOneBy([
                'type' => $typeName,
            ]);

            if (!$existingType) {
                $gameType = new GameTypes();
                $gameType->setType($typeName);
                $entityManager->persist($gameType);
            }
        }

        $entityManager->flush();
    }
}
