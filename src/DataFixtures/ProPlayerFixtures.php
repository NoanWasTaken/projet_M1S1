<?php
namespace App\DataFixtures;

use App\Entity\ProPlayer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProPlayerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $players = [
            [
                'name' => 'TenZ',
                'role' => 'Duelist',
                'team' => 'Sentinels',
                'country' => 'Canada',
                'photo' => 'https://img.redbull.com/images/c_limit,w_1500,h_1000/f_auto,q_auto/redbullcom/2025/6/16/sszyzfwxoqnbcfpxagjl/tyson-tenz-ngo',
                'game' => 'Valorant',
                'mouse' => 'Finalmouse Starlight-12',
                'keyboard' => 'Wooting 60HE',
                'headset' => 'Sennheiser HD 700',
                'description' => "TenZ est un joueur professionnel canadien de Valorant, reconnu pour ses mécaniques exceptionnelles et son style agressif. Il joue pour Sentinels et inspire de nombreux fans par ses performances et sa personnalité attachante.",
            ],
            [
                'name' => 'Zekken',
                'role' => 'Flex',
                'team' => 'Sentinels',
                'country' => 'USA',
                'photo' => 'https://www.prosettings.gg/wp-content/uploads/2023/10/zekken-valorant-player-profile-picture-new.webp',
                'game' => 'Valorant',
                'mouse' => 'Logitech G Pro X Superlight',
                'keyboard' => 'SteelSeries Apex Pro',
                'headset' => 'Beyerdynamic DT 770 Pro',
                'description' => "Zekken est un jeune talent américain de Valorant, connu pour sa polyvalence et sa capacité à s'adapter à tous les rôles. Il évolue chez Sentinels et impressionne par sa rapidité d'exécution.",
            ],
            [
                'name' => 'ZywOo',
                'role' => 'AWPer',
                'team' => 'Vitality',
                'country' => 'France',
                'photo' => 'https://www.lequipe.fr/_medias/img-photo-jpg/mathieu-zywoo-herbaut-mvp-et-vainqueur-du-major-de-paris-avec-vitality-stephanie-lindgren-blast/1500000001787656/211:37,1787:1088-1200-800-75/277ee.jpg',
                'game' => 'CSGO',
                'mouse' => 'VAXEE OUTSET AX',
                'keyboard' => 'Wooting 60HE',
                'headset' => 'Sennheiser HD 599',
                'description' => "ZywOo est considéré comme l'un des meilleurs joueurs de CS:GO au monde. MVP de nombreux tournois, il brille par sa précision et son calme sur la scène internationale avec Vitality.",
            ],
            [
                'name' => 'Faker',
                'role' => 'Midlaner',
                'team' => 'T1',
                'country' => 'Corée',
                'photo' => 'https://img.redbull.com/images/c_limit,w_1500,h_1000/f_auto,q_auto/redbullcom/2024/11/14/ts2357pjyhrekoexmnjy/faker-red-bull-cover-story',
                'game' => 'LoL',
                'mouse' => 'Razer DeathAdder V3 Pro',
                'keyboard' => 'Corsair K70 RGB',
                'headset' => 'HyperX Cloud II',
                'description' => "Faker est une légende de League of Legends, triple champion du monde avec T1. Il est reconnu pour son intelligence de jeu et son influence sur la scène e-sport mondiale.",
            ],
            [
                'name' => 'MrSavage',
                'role' => 'Builder',
                'team' => '100 Thieves',
                'country' => 'Norvège',
                'photo' => 'https://prosettings.net/wp-content/uploads/mrsavage.png',
                'game' => 'Fortnite',
                'mouse' => 'Logitech G Pro Wireless',
                'keyboard' => 'SteelSeries Apex Pro',
                'headset' => 'Beyerdynamic DT 990 Pro',
                'description' => "MrSavage est un prodige norvégien de Fortnite, célèbre pour ses constructions rapides et ses stratégies innovantes. Il joue pour 100 Thieves et inspire la nouvelle génération de joueurs.",
            ],
            [
                'name' => 'Squeezie',
                'role' => 'Influenceur',
                'team' => 'Solo',
                'country' => 'France',
                'photo' => 'https://pbs.twimg.com/profile_images/1596144145298513920/3dcRgV9L_400x400.jpg',
                'game' => 'Influenceur',
                'mouse' => 'Logitech G Pro X Superlight',
                'keyboard' => 'SteelSeries Apex Pro',
                'headset' => 'Beyerdynamic DT 770 Pro',
                'description' => "Squeezie est le plus grand créateur de contenu francophone, connu pour ses vidéos divertissantes et ses événements gaming. Il influence des millions de fans à travers le monde.",
            ],
        ];

        foreach ($players as $data) {
            $player = new ProPlayer();
            $player->setName($data['name'])
                ->setRole($data['role'])
                ->setTeam($data['team'])
                ->setCountry($data['country'])
                ->setPhoto($data['photo'])
                ->setGame($data['game'])
                ->setMouse($data['mouse'])
                ->setKeyboard($data['keyboard'])
                ->setHeadset($data['headset'])
                ->setDescription($data['description']);
            $manager->persist($player);
        }
        $manager->flush();
    }
}
