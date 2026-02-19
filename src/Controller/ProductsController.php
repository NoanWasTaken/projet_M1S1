<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    public function __construct(
        private ProductsRepository $productRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/catalogue', name: 'app_catalogue')]
    public function catalogue(Request $request): Response
    {
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        if ($search) {
            $products = $this->productRepository->searchProducts($search);
        } elseif ($category) {
            $products = $this->productRepository->findByCategory($category);
        } else {
            $products = $this->productRepository->findAvailableProducts();
        }

        $categories = $this->productRepository->getAvailableCategories();

        return $this->render('product/catalogue.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $category,
            'searchQuery' => $search,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, Request $request): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour laisser un avis.');
                return $this->redirectToRoute('app_login');
            }

            $review->setProduct($product);
            $review->setAuthor($this->getUser());
            $this->entityManager->persist($review);
            $this->entityManager->flush();

            $this->productRepository->updateAverageRating($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre avis a été publié !');
            return $this->redirectToRoute('app_product_detail', ['id' => $id]);
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'reviewForm' => $form->createView(),
        ]);
    }
}
