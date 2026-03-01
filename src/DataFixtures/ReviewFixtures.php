<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function getDependencies(): array
    {
        return [ProductsFixtures::class, UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // créer des utilisateurs de test
        $usersData = [
            ['name' => 'Lucas', 'surname' => 'Martin', 'email' => 'lucas.martin@example.com'],
            ['name' => 'Emma', 'surname' => 'Bernard', 'email' => 'emma.bernard@example.com'],
            ['name' => 'Hugo', 'surname' => 'Dubois', 'email' => 'hugo.dubois@example.com'],
            ['name' => 'Chloé', 'surname' => 'Petit', 'email' => 'chloe.petit@example.com'],
        ];

        $users = [];
        foreach ($usersData as $userData) {
            $user = new User();
            $user->setName($userData['name']);
            $user->setSurname($userData['surname']);
            $user->setEmail($userData['email']);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123!'));
            $user->setIsVerified(true);
            $manager->persist($user);
            $users[] = $user;
        }


        $admin = $this->getReference('user_admin', User::class);

        $reviewsData = [
            0 => [
                [5, 'Souris parfaite pour le FPS compétitif. Ultra légère, capteur incroyable. Je ne la changerai jamais.', 0],
                [5, 'La meilleure souris que j\'ai jamais utilisée. Wireless sans latence, autonomie excellente.', 1],
                [4, 'Qualité premium mais prix élevé. Bonne prise en main pour les grandes mains.', 2],
                [5, 'Indispensable pour jouer à haut niveau. Investissement qui vaut totalement le coup.', -1],
                [4, 'Très satisfait, légère et précise. Seul bémol : pas de RGB.', null],
            ],
            1 => [
                [5, 'Design ergonomique parfait pour les droitiers longues sessions. Capteur ultra précis.', 1],
                [4, 'Excellente souris, connexion Hyperspeed très stable. Un peu cher mais ça vaut le prix.', 3],
                [5, 'Après 6 mois d\'utilisation, toujours aussi performante. Razer au top.', 0],
                [3, 'Bonne souris mais la forme ne convient pas aux petites mains. Capteur excellent.', null],
            ],
            2 => [
                [5, 'Meilleur rapport qualité/prix du marché ! Pour les petits budgets, c\'est parfait.', 2],
                [4, 'Très bon capteur pour ce prix. Filaire solide et léger.', 0],
                [4, 'Parfaite pour débuter en gaming. Simple, efficace, robuste.', null],
                [3, 'Correcte mais après avoir essayé des souris haut de gamme, on sent la différence.', -1],
                [5, 'Souris budget imbattable. Bout d\'un an, toujours nickel.', 3],
            ],
            3 => [
                [5, 'Clavier de compétition. Les Cherry MX Red sont un délice pour le gaming, feedback parfait.', -1],
                [5, 'Construction en aluminium solide, RGB magnifique. Mon clavier depuis 2 ans.', 1],
                [4, 'Excellent clavier mécanique full-size. Petit regret : pas de mode sans fil.', 2],
                [5, 'Le son des touches est satisfaisant, la construction est premium. Très heureux de cet achat.', 0],
                [4, 'Parfait pour le gaming et le travail. RGB personnalisable à l\'infini.', null],
            ],
            4 => [
                [5, 'La molette multifonction change tout ! Je l\'utilise pour les raccourcis dans mes jeux.', 3],
                [5, 'Switches Razer Green très tactiles, excellent retour au toucher. Construction irréprochable.', 0],
                [4, 'Le summum du clavier gaming. Très cher mais chaque centime est justifié.', 1],
                [4, 'Repose-poignet magnétique très pratique. RGB Chroma compatible avec tous mes périphériques Razer.', null],
            ],
            5 => [
                [5, 'Format TKL idéal pour gagner de la place sur le bureau. Hot-swap vraiment pratique.', 2],
                [4, 'Excellent clavier compact. Les switches GX Brown sont parfaits pour un usage mixte gaming/bureautique.', -1],
                [5, 'Mon clavier de tournoi. Fiable, précis, transportable facilement.', 3],
                [4, 'Qualité Logitech impeccable. J\'ai mis des switches GX Blue, le bruit est satisfaisant.', null],
                [3, 'Bon clavier mais pas de pavé numérique peut être gênant selon les jeux.', 1],
            ],
            6 => [
                [5, 'Son incroyable pour le prix ! La double chambre acoustique fait vraiment la différence.', 0],
                [5, 'Le meilleur casque que j\'ai eu sous les 100€. Confort exceptionnel, je l\'oublie sur la tête.', 2],
                [4, 'Excellent casque filaire. Micro détachable pratique. Idéal pour les longues sessions.', -1],
                [3, 'Bon casque mais le câble est un peu rigide. Son de qualité pour le prix.', null],
                [5, 'Je recommande à tous les gamers à petit budget. Vraiment aucun regret.', 3],
            ],
            7 => [
                [5, 'Audio haute résolution absolument époustouflant. Le DAC inclus fait une vraie différence.', 1],
                [5, 'Le top du top. Spatial Audio 360° fonctionne à merveille dans les jeux compétitifs.', 3],
                [4, 'Très bon casque premium. Micro ClearCast Gen 2 parfait pour les streams. Cher mais justifié.', 0],
                [4, 'Compatible PC, PS5 et Switch : parfait pour les multi-platformers. Confort top.', -1],
                [5, 'Investissement qui vaut le coup. La qualité audio est dans une autre dimension.', null],
            ],
            8 => [
                [4, 'Le design blanc/lavande est magnifique. Son de qualité et autonomie impressionnante.', 2],
                [5, 'Micro Blue VO!CE exceptionnel, ma voix est ultra claire en vocal. Casque parfait.', 0],
                [4, 'Bluetooth + Lightspeed, c\'est très pratique. J\'alterne entre PC et téléphone sans problème.', 3],
                [3, 'Beau casque mais je l\'attendais un peu plus en termes de basses. Confort irréprochable.', null],
            ],
            9 => [
                [5, 'Le retour haptique est bluffant ! Les explosions, les tirs... tout se ressent dans le casque.', 1],
                [4, 'THX Spatial Audio excellent dans les jeux AAA. L\'immersion est totale avec le haptic.', 2],
                [5, 'Expérience de jeu révolutionnée. Je ne peux plus jouer sans vibrations maintenant.', -1],
                [4, 'RGB Chroma magnifique, son spatial de qualité. Le câble USB est un peu court.', null],
                [5, 'Casque de ouf pour l\'immersion. Configuration facile avec Razer Synapse.', 3],
            ],
        ];

        $products = [];
        for ($i = 0; $i <= 9; $i++) {
            $products[$i] = $this->getReference('product_' . $i, \App\Entity\Products::class);
        }

        foreach ($reviewsData as $productIndex => $reviews) {
            $product = $products[$productIndex];

            foreach ($reviews as [$rating, $comment, $userIndex]) {
                $review = new Review();
                $review->setRating($rating);
                $review->setComment($comment);

                if ($userIndex === -1) {
                    $review->setAuthor($admin);
                } elseif ($userIndex !== null) {
                    $review->setAuthor($users[$userIndex]);
                } else {
                    // user random 
                    $review->setAuthor($users[array_rand($users)]);
                }

                // dates sur 6 mois pour reviews
                $daysAgo = random_int(1, 180);
                $review->setCreatedAt(new \DateTimeImmutable('-' . $daysAgo . ' days'));

                $product->addReview($review);
                $manager->persist($review);
            }
        }

        $manager->flush();

        // calcul moyenne pour produits
        foreach ($products as $product) {
            $product->computeAverageRating();
        }

        $manager->flush();
    }
}
