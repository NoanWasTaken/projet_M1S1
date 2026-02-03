<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository
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
    public function detail(int $id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
        ]);
    }
}
