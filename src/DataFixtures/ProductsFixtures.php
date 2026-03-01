<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            // Souris
            [
                'name' => 'Finalmouse Starlight-12',
                'description' => "La Finalmouse Starlight-12 est une souris gaming ultra-légère en alliage de magnésium, conçue pour une précision extrême et une rapidité inégalée. Son capteur haute performance et sa forme ergonomique offrent un confort optimal pour les longues sessions de jeu.",
                'price' => 189.99,
                'category' => 'souris',
                'brand' => 'Finalmouse',
                'stock' => 10,
                'rating' => '4.9',
                'photos' => ['https://prosettings.net/wp-content/uploads/finalmouse-starlight-12.jpg'],
            ],
            [
                'name' => 'Logitech G Pro X Superlight',
                'description' => "La Logitech G Pro X Superlight est une souris sans fil ultra-légère dotée d'un capteur HERO 25K, offrant une précision et une réactivité exceptionnelles. Sa conception minimaliste et sa autonomie longue durée en font le choix idéal des joueurs professionnels.",
                'price' => 149.99,
                'category' => 'souris',
                'brand' => 'Logitech',
                'stock' => 15,
                'rating' => '4.8',
                'photos' => ['https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800'],
            ],
            [
                'name' => 'VAXEE OUTSET AX',
                'description' => "La VAXEE OUTSET AX est une souris filaire conçue pour l'e-sport, avec une prise en main confortable et un capteur optique de haute précision. Sa robustesse et sa simplicité séduisent les joueurs exigeants.",
                'price' => 79.99,
                'category' => 'souris',
                'brand' => 'VAXEE',
                'stock' => 8,
                'rating' => '4.7',
                'photos' => ['https://prosettings.net/wp-content/uploads/vaxee-outset-ax.png'],
            ],
            [
                'name' => 'Razer DeathAdder V3 Pro',
                'description' => "La Razer DeathAdder V3 Pro est une souris ergonomique sans fil, dotée d'un capteur Focus Pro 30K et d'une autonomie impressionnante. Sa forme iconique assure une prise en main naturelle et confortable.",
                'price' => 139.99,
                'category' => 'souris',
                'brand' => 'Razer',
                'stock' => 22,
                'rating' => '4.7',
                'photos' => ['https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=800'],
            ],
            [
                'name' => 'Logitech G Pro Wireless',
                'description' => "La Logitech G Pro Wireless combine légèreté, capteur HERO et technologie sans fil Lightspeed pour une expérience de jeu fluide et sans latence. Sa conception ambidextre convient à tous les styles de prise en main.",
                'price' => 129.99,
                'category' => 'souris',
                'brand' => 'Logitech',
                'stock' => 12,
                'rating' => '4.6',
                'photos' => ['https://prosettings.net/wp-content/uploads/logitech-g-pro-wireless.png'],
            ],
            // Claviers
            [
                'name' => 'Wooting 60HE',
                'description' => "Le Wooting 60HE est un clavier analogique révolutionnaire, offrant une détection de pression sur chaque touche pour un contrôle précis en jeu. Compact et personnalisable, il s'adresse aux gamers à la recherche d'innovation.",
                'price' => 179.99,
                'category' => 'clavier',
                'brand' => 'Wooting',
                'stock' => 10,
                'rating' => '4.9',
                'photos' => ['https://i.ebayimg.com/images/g/31gAAOSwBmZlqK-1/s-l1200.jpg'],
            ],
            [
                'name' => 'SteelSeries Apex Pro',
                'description' => "Le SteelSeries Apex Pro est un clavier mécanique RGB doté de switches OmniPoint ajustables, permettant de personnaliser la sensibilité de chaque touche. Son châssis en aluminium et son éclairage dynamique en font un choix haut de gamme.",
                'price' => 199.99,
                'category' => 'clavier',
                'brand' => 'SteelSeries',
                'stock' => 20,
                'rating' => '4.8',
                'photos' => ['https://prosettings.net/wp-content/uploads/steelseries-apex-pro.png'],
            ],
            [
                'name' => 'Corsair K70 RGB',
                'description' => "Le Corsair K70 RGB est un clavier mécanique robuste avec switches Cherry MX, rétroéclairage RGB personnalisable et repose-poignets confortable. Idéal pour les gamers exigeants et les longues sessions.",
                'price' => 149.99,
                'category' => 'clavier',
                'brand' => 'Corsair',
                'stock' => 14,
                'rating' => '4.7',
                'photos' => ['https://prosettings.net/wp-content/uploads/corsair-k70-rgb.png'],
            ],
            // Casques
            [
                'name' => 'Sennheiser HD 700',
                'description' => "Le Sennheiser HD 700 est un casque audio haut de gamme offrant une restitution sonore exceptionnelle, des basses profondes et un confort optimal grâce à ses coussinets en velours. Parfait pour l'écoute et le gaming immersif.",
                'price' => 399.99,
                'category' => 'casque',
                'brand' => 'Sennheiser',
                'stock' => 5,
                'rating' => '4.9',
                'photos' => ['https://prosettings.net/wp-content/uploads/sennheiser-hd-700.png'],
            ],
            [
                'name' => 'Beyerdynamic DT 770 Pro',
                'description' => "Le Beyerdynamic DT 770 Pro est un casque studio fermé réputé pour sa clarté sonore et son isolation. Idéal pour le monitoring, le streaming et le gaming professionnel.",
                'price' => 139.99,
                'category' => 'casque',
                'brand' => 'Beyerdynamic',
                'stock' => 18,
                'rating' => '4.8',
                'photos' => ['https://prosettings.net/wp-content/uploads/beyerdynamic-dt-770-pro.png'],
            ],
            [
                'name' => 'Sennheiser HD 599',
                'description' => "Le Sennheiser HD 599 est un casque audio ouvert offrant une scène sonore large et naturelle, un confort supérieur et une qualité de fabrication premium. Parfait pour les audiophiles et les gamers.",
                'price' => 199.99,
                'category' => 'casque',
                'brand' => 'Sennheiser',
                'stock' => 7,
                'rating' => '4.7',
                'photos' => ['https://prosettings.net/wp-content/uploads/sennheiser-hd-599.png'],
            ],
            [
                'name' => 'HyperX Cloud II',
                'description' => "Le HyperX Cloud II est un casque gaming polyvalent, doté d'un son surround virtuel 7.1, d'un micro détachable et d'un confort exceptionnel. Un incontournable pour les joueurs compétitifs.",
                'price' => 99.99,
                'category' => 'casque',
                'brand' => 'HyperX',
                'stock' => 25,
                'rating' => '4.7',
                'photos' => ['https://prosettings.net/wp-content/uploads/hyperx-cloud-ii.png'],
            ],
            [
                'name' => 'Beyerdynamic DT 990 Pro',
                'description' => "Le Beyerdynamic DT 990 Pro est un casque studio ouvert reconnu pour sa restitution sonore détaillée et son confort longue durée. Idéal pour le mixage, le streaming et le jeu immersif.",
                'price' => 159.99,
                'category' => 'casque',
                'brand' => 'Beyerdynamic',
                'stock' => 10,
                'rating' => '4.7',
                'photos' => ['https://prosettings.net/wp-content/uploads/beyerdynamic-dt-990-pro.png'],
            ],
        ];

        foreach ($products as $i => $productData) {
            $product = new Products();
            $product->setName($productData['name'])
                ->setDescription($productData['description'])
                ->setPrice($productData['price'])
                ->setCategory($productData['category'])
                ->setBrand($productData['brand'])
                ->setStock($productData['stock'])
                ->setRating($productData['rating'])
                ->setPhotos(isset($productData['photos']) ? $productData['photos'] : [$productData['image'] ?? null])
                ->setSpecifications($productData['specifications'] ?? null)
                ->setIsAvailable(true)
                ->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($product);
            // Ajoute la référence pour les 10 premiers produits (utilisés dans ReviewFixtures)
            if ($i < 10) {
                $this->addReference('product_' . $i, $product);
            }
        }
        $manager->flush();
    }
}
