<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Souris Gaming
        $products = [
            // SOURIS
            [
                'name' => 'Logitech G Pro X Superlight',
                'description' => 'Souris gaming sans fil ultra-légère, capteur HERO 25K, 25 600 DPI, autonomie de 70 heures. Parfaite pour les joueurs FPS professionnels.',
                'price' => '149.99',
                'category' => 'souris',
                'brand' => 'Logitech',
                'stock' => 15,
                'rating' => '4.8',
                'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800',
                'specifications' => [
                    'type' => 'Sans fil',
                    'capteur' => 'HERO 25K',
                    'dpi' => '25 600',
                    'poids' => '63g',
                    'autonomie' => '70 heures',
                    'connectivite' => 'Lightspeed USB',
                ]
            ],
            [
                'name' => 'Razer DeathAdder V3 Pro',
                'description' => 'Souris gaming sans fil ergonomique, capteur Focus Pro 30K, jusqu\'à 30 000 DPI. Design ergonomique pour droitier.',
                'price' => '139.99',
                'category' => 'souris',
                'brand' => 'Razer',
                'stock' => 22,
                'rating' => '4.7',
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=800',
                'specifications' => [
                    'type' => 'Sans fil',
                    'capteur' => 'Focus Pro 30K',
                    'dpi' => '30 000',
                    'poids' => '64g',
                    'autonomie' => '90 heures',
                    'connectivite' => 'Razer HyperSpeed Wireless',
                ]
            ],
            [
                'name' => 'SteelSeries Rival 3',
                'description' => 'Souris gaming filaire abordable avec capteur TrueMove Core, jusqu\'à 8 500 CPI. Excellent rapport qualité-prix.',
                'price' => '39.99',
                'category' => 'souris',
                'brand' => 'SteelSeries',
                'stock' => 30,
                'rating' => '4.5',
                'image' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=800',
                'specifications' => [
                    'type' => 'Filaire',
                    'capteur' => 'TrueMove Core',
                    'dpi' => '8 500',
                    'poids' => '77g',
                    'cable' => '2 mètres',
                    'boutons' => '6 boutons programmables',
                ]
            ],

            // CLAVIERS
            [
                'name' => 'Corsair K70 RGB PRO',
                'description' => 'Clavier mécanique gaming full-size avec switches Cherry MX, rétroéclairage RGB personnalisable. Construction en aluminium.',
                'price' => '179.99',
                'category' => 'clavier',
                'brand' => 'Corsair',
                'stock' => 18,
                'rating' => '4.9',
                'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=800',
                'specifications' => [
                    'type' => 'Mécanique',
                    'switches' => 'Cherry MX Red/Brown/Blue',
                    'format' => 'Full-size (100%)',
                    'retroeclairage' => 'RGB per-key',
                    'connectivite' => 'USB-C détachable',
                    'materiau' => 'Aluminium brossé',
                ]
            ],
            [
                'name' => 'Razer BlackWidow V4 Pro',
                'description' => 'Clavier mécanique premium avec switches Razer Green, molette multifonction, repose-poignet magnétique. Le summum du gaming.',
                'price' => '229.99',
                'category' => 'clavier',
                'brand' => 'Razer',
                'stock' => 12,
                'rating' => '4.8',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800',
                'specifications' => [
                    'type' => 'Mécanique',
                    'switches' => 'Razer Green Mechanical',
                    'format' => 'Full-size avec molette',
                    'retroeclairage' => 'Razer Chroma RGB',
                    'connectivite' => 'USB-C + Sans fil',
                    'autonomie' => '200 heures',
                ]
            ],
            [
                'name' => 'Logitech G Pro X TKL',
                'description' => 'Clavier mécanique compact TKL (sans pavé numérique) avec switches GX interchangeables. Idéal pour le gaming compétitif.',
                'price' => '159.99',
                'category' => 'clavier',
                'brand' => 'Logitech',
                'stock' => 25,
                'rating' => '4.7',
                'image' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=800',
                'specifications' => [
                    'type' => 'Mécanique',
                    'switches' => 'GX Blue/Brown/Red (hot-swap)',
                    'format' => 'TKL (80%)',
                    'retroeclairage' => 'RGB Lightsync',
                    'connectivite' => 'USB détachable',
                    'poids' => '980g',
                ]
            ],

            // CASQUES
            [
                'name' => 'HyperX Cloud Alpha',
                'description' => 'Casque gaming stéréo avec double chambre acoustique pour un son distinctif. Confortable pour de longues sessions.',
                'price' => '99.99',
                'category' => 'casque',
                'brand' => 'HyperX',
                'stock' => 28,
                'rating' => '4.6',
                'image' => 'https://images.unsplash.com/photo-1599669454699-248893623440?w=800',
                'specifications' => [
                    'type' => 'Filaire',
                    'audio' => 'Stéréo 2.0',
                    'drivers' => '50mm',
                    'micro' => 'Détachable antibruit',
                    'connectique' => 'Jack 3.5mm + adaptateur PC',
                    'poids' => '298g',
                ]
            ],
            [
                'name' => 'SteelSeries Arctis Nova Pro',
                'description' => 'Casque gaming premium avec DAC haute fidélité, son spatial 360°, micro ClearCast Gen 2. Audio immersif haute résolution.',
                'price' => '349.99',
                'category' => 'casque',
                'brand' => 'SteelSeries',
                'stock' => 8,
                'rating' => '4.9',
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800',
                'specifications' => [
                    'type' => 'Filaire avec DAC',
                    'audio' => '360° Spatial Audio',
                    'drivers' => '40mm Premium',
                    'micro' => 'ClearCast Gen 2 bidirectionnel',
                    'connectique' => 'USB + Jack 3.5mm',
                    'compatibilite' => 'PC, PS5, Xbox, Switch',
                ]
            ],
            [
                'name' => 'Logitech G735 Wireless',
                'description' => 'Casque sans fil léger avec design blanc/lavande, son surround LIGHTSYNC RGB, micro Blue VO!CE. 56 heures d\'autonomie.',
                'price' => '229.99',
                'category' => 'casque',
                'brand' => 'Logitech',
                'stock' => 14,
                'rating' => '4.5',
                'image' => 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=800',
                'specifications' => [
                    'type' => 'Sans fil',
                    'audio' => 'DTS Headphone:X 2.0',
                    'drivers' => '40mm',
                    'micro' => 'Blue VO!CE détachable',
                    'autonomie' => '56 heures',
                    'connectivite' => 'Lightspeed USB + Bluetooth',
                ]
            ],
            [
                'name' => 'Razer Kraken V3 HyperSense',
                'description' => 'Casque gaming avec retour haptique, THX Spatial Audio, éclairage RGB Chroma. Immersion totale avec vibrations intelligentes.',
                'price' => '129.99',
                'category' => 'casque',
                'brand' => 'Razer',
                'stock' => 20,
                'rating' => '4.6',
                'image' => 'https://images.unsplash.com/photo-1545127398-14699f92334b?w=800',
                'specifications' => [
                    'type' => 'Filaire USB',
                    'audio' => 'THX Spatial Audio',
                    'drivers' => 'TriForce 50mm',
                    'micro' => 'HyperClear Cardioid',
                    'haptic' => 'HyperSense Technology',
                    'rgb' => 'Razer Chroma RGB',
                ]
            ],
        ];

        foreach ($products as $productData) {
            $product = new Products();
            $product->setName($productData['name'])
                ->setDescription($productData['description'])
                ->setPrice($productData['price'])
                ->setCategory($productData['category'])
                ->setBrand($productData['brand'])
                ->setStock($productData['stock'])
                ->setRating($productData['rating'])
                ->setPhotos([$productData['image']])
                ->setSpecifications($productData['specifications'])
                ->setIsAvailable(true);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
