<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\GameTypes;
use App\Entity\PlayerProfile;
use App\Entity\ProPlayer;
use App\Entity\Products;
use App\Entity\Review;
use App\Entity\Reward;
use App\Entity\User;
use App\Entity\UserReward;
use App\Entity\XPEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/content.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GearForge Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour à l\'accueil', 'fa fa-arrow-left', 'app_home');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Profils joueur', 'fas fa-user-circle', PlayerProfile::class);

        yield MenuItem::section('Boutique');
        yield MenuItem::linkToCrud('Produits', 'fas fa-box', Products::class);
        yield MenuItem::linkToCrud('Types de jeux', 'fas fa-gamepad', GameTypes::class);
        yield MenuItem::linkToCrud('Avis', 'fas fa-star', Review::class);
        yield MenuItem::linkToCrud('Paniers', 'fas fa-shopping-cart', Cart::class);
        yield MenuItem::linkToCrud('Articles de panier', 'fas fa-list', CartItem::class);

        yield MenuItem::section('Pro Players');
        yield MenuItem::linkToCrud('Pro Players', 'fas fa-trophy', ProPlayer::class);

        yield MenuItem::section('Récompenses & XP');
        yield MenuItem::linkToCrud('Récompenses', 'fas fa-gift', Reward::class);
        yield MenuItem::linkToCrud('Récompenses utilisateurs', 'fas fa-medal', UserReward::class);
        yield MenuItem::linkToCrud('Événements XP', 'fas fa-bolt', XPEvent::class);
    }
}

