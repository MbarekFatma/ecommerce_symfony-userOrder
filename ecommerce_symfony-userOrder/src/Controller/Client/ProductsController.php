<?php

namespace App\Controller\Client;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductsController extends AbstractController
{
    private $productRepository;
    private $categoryRepository;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ManagerRegistry $doctrine)
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }



    /**
     * @Route("/", name="home")
     */

    public function home(): Response
    {

        $categories = $this->categoryRepository->findAll();
        return $this->render('homePrincipale/homePrincipale.html.twig', [

            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }
    /**
     * @Route("/pro", name="product_index")
     */

    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        return $this->render('products/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }
    #[Route('/product/{category}', name: 'product_category')]

    public function categoryProducts(Category $category): Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('products/index.html.twig', [
            'products' => $category->getProducts(),
            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }
    #[Route('/product/details/{id}', name: 'product_show')]
    public function show(Product $product): Response
    {
        return $this->render('products/show.html.twig', [
            'product' => $product,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }

    /**
     * @Route("/category/{categoryId}/products", name="category_products")
     */
    public function categoryProductsAction($categoryId): Response
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        $products = $category->getProducts();

        $categories = $this->categoryRepository->findAll();

        return $this->render('products/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'photo_url' => 'http://127.0.0.1:8000/uploads/'
        ]);
    }
    /**
     * @Route("/produits/{categorie}", name="afficher_produits_par_categorie")
     */
    public function afficherProduitsParCategorie(ProductRepository $productRepository, CategoryRepository $categoryRepository, $categorie): Response
    {
        $category = $categoryRepository->findOneBy(['name' => $categorie]);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        $products = $productRepository->findBy(['category' => $category]);

        return $this->render('products/index.html.twig', [
            'products' => $products,
        ]);
    }



}