<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\ChatConversation;
use App\Entity\GameTypes;
use App\Entity\Order;
use App\Entity\PlayerProfile;
use App\Entity\ProPlayer;
use App\Entity\Products;
use App\Entity\Review;
use App\Entity\Reward;
use App\Entity\User;
use App\Entity\UserReward;
use App\Entity\XPEvent;
use App\Repository\ChatConversationRepository;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use App\Service\StockAlertService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MODERATOR')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ChatConversationRepository $conversationRepository,
        private AdminUrlGenerator          $adminUrlGenerator,
        private ProductsRepository         $productsRepository,
        private StockAlertService          $stockAlert,
        private CsrfTokenManagerInterface  $csrfTokenManager,
        private EntityManagerInterface     $em,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $threshold = (int) ($_ENV['STOCK_ALERT_THRESHOLD'] ?? 10);
        $allProducts = $this->productsRepository->findAll();

        $outOfStock  = array_filter($allProducts, fn ($p) => $p->getStock() === 0);
        $lowStock    = array_filter($allProducts, fn ($p) => $p->getStock() > 0 && $p->getStock() < $threshold);
        $okStock     = array_filter($allProducts, fn ($p) => $p->getStock() >= $threshold);

        $revenue = (float) $this->em->createQuery(
            "SELECT COALESCE(SUM(o.total), 0) FROM App\\Entity\\Order o WHERE o.status NOT IN ('pending', 'cancelled')"
        )->getSingleScalarResult();

        $ordersByStatus = [];
        foreach ($this->em->createQuery(
            'SELECT o.status AS status, COUNT(o.id) AS cnt FROM App\\Entity\\Order o GROUP BY o.status'
        )->getResult() as $row) {
            $key = $row['status'] instanceof \BackedEnum ? $row['status']->value : (string) $row['status'];
            $ordersByStatus[$key] = (int) $row['cnt'];
        }

        $since7days = new \DateTimeImmutable('-7 days');
        $newOrders7d = (int) $this->em->createQuery(
            'SELECT COUNT(o.id) FROM App\\Entity\\Order o WHERE o.createdAt >= :since'
        )->setParameter('since', $since7days)->getSingleScalarResult();

        $totalOrders = array_sum($ordersByStatus);

        $totalUsers = (int) $this->em->createQuery(
            'SELECT COUNT(u.id) FROM App\\Entity\\User u'
        )->getSingleScalarResult();

        return $this->render('admin/dashboard.html.twig', [
            'threshold' => $threshold,
            'total' => count($allProducts),
            'out_of_stock'=> count($outOfStock),
            'low_stock' => count($lowStock),
            'ok_stock' => count($okStock),
            'revenue'          => round($revenue, 2),
            'orders_by_status' => $ordersByStatus,
            'total_orders'     => $totalOrders,
            'new_orders_7d'    => $newOrders7d,
            'total_users'      => $totalUsers,
        ]);
    }


    #[Route('/admin/stock/check', name: 'admin_stock_check', methods: ['POST'])]
    public function checkStocks(Request $request): RedirectResponse
    {
        $token = new CsrfToken('stock_check', (string) $request->request->get('_token'));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('danger', 'Token CSRF invalide. Veuillez recharger la page.');
            return $this->redirectToRoute('admin');
        }

        $result = $this->stockAlert->checkAll();

        if ($result['alerts'] === 0) {
            $this->addFlash(
                'success',
                'Vérification terminée — tous les stocks sont au-dessus du seuil (' . $result['threshold'] . ' unités).'
            );
        } else {
            $names = array_map(fn ($p) => $p['name'] . ' (' . $p['stock'] . ')', $result['products']);
            $this->addFlash(
                'warning',
                '' . $result['alerts'] . ' produit(s) en alerte — notifications envoyées à Discord & Telegram : '
                . implode(', ', $names) . '.'
            );
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/user/{userId}/conversations', name: 'admin_user_conversations')]
    public function userConversations(int $userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $conversations = $this->conversationRepository->findByUser($user);

        return $this->render('admin/chat/conversations.html.twig', [
            'user' => $user,
            'conversations' => $conversations,
        ]);
    }

    #[Route('/admin/conversation/{id}', name: 'admin_chat_conversation_detail')]
    public function conversationDetail(int $id): Response
    {
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw $this->createNotFoundException('Conversation introuvable.');
        }

        $backUrl = $conversation->getUser()
            ? $this->generateUrl('admin_user_conversations', ['userId' => $conversation->getUser()->getId()])
            : $this->adminUrlGenerator
                ->setController(ChatConversationCrudController::class)
                ->generateUrl();

        return $this->render('admin/chat/conversation_detail.html.twig', [
            'conversation' => $conversation,
            'backUrl' => $backUrl,
        ]);
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

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Utilisateurs');
            yield MenuItem::linkToCrud('Users', 'fas fa-users', User::class);
            yield MenuItem::linkToCrud('Profils joueur', 'fas fa-user-circle', PlayerProfile::class);
            yield MenuItem::linkToCrud('Conversations chatbot', 'fas fa-comments', ChatConversation::class);
        }

        yield MenuItem::section('Boutique');
        yield MenuItem::linkToCrud('Produits', 'fas fa-box', Products::class);
        yield MenuItem::linkToCrud('Commandes', 'fas fa-receipt', Order::class);

        if ($this->isGranted('ROLE_ADMIN')) {
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
}

