<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CategoryController extends AbstractController
{
    /**
     * This controller display all category
     *
     * @param CategoryRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }
    #[Route('/category', name: 'category.index', methods: ['GET']),isGranted("ROLE_ADMIN")]
    public function index(
        CategoryRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $categorys = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/category/index.html.twig', [
            'categorys' => $categorys
        ]);
    }

    /**
     * This controller show a form which create a category
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/category/creation', name: 'category.new', methods: ['GET', 'POST']), isGranted("ROLE_ADMIN")]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            if ($request->files->get('category') && $request->files->get('category')['image']) {
                $image = $request->files->get('category')['image'];
                $image_name =time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'),$image_name);
                $category->setImage($image_name);
            }

            $manager->persist($category);
            $manager->flush();

            $this->addFlash(
                'success',
                'Category is created successfully'
            );

            return $this->redirectToRoute('category.index');
        }

        return $this->render('pages/category/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow us to edit a category
     *
     * @param Category $category
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/category/edition/{id}', 'category.edit', methods: ['GET', 'POST']), isGranted("ROLE_ADMIN")]
    public function edit(
        Category $category,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            if ($request->files->get('category') && $request->files->get('category')['image']) {
                $image = $request->files->get('category')['image'];
                $image_name =time().'_'.$image->getClientOriginalName();
                $image->move($this->getParameter('image_directory'),$image_name);
                $category->setImage($image_name);
            }
            $manager->persist($category);
            $manager->flush();

            $this->addFlash(
                'success',
                'Category is successfully modified !'
            );

            return $this->redirectToRoute('category.index');
        }

        return $this->render('pages/category/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allows us to delete a category
     *
     * @param EntityManagerInterface $manager
     * @param Category $category
     * @return Response
     */
    #[Route('/category/suppression/{id}', 'category.delete', methods: ['GET']), isGranted("ROLE_ADMIN")]

    public function delete(
        EntityManagerInterface $manager,
        Category $category
    ): Response {
        $manager->remove($category);
        $manager->flush();

        $this->addFlash(
            'success',
            'Category is successfully deleted !'
        );

        return $this->redirectToRoute('category.index');
    }


    /*=== Search Bar ===*/
    public function searchForm (){
        $form= $this->createFormBuilder()
            ->setAction($this->generateUrl('Search'))
            ->setMethod('POST')
            ->add('text',TextType::class,[

                'attr'=>[
                    'placeholder'=>'Search category',
                    'required'=>false,
                    'class' => 'form-control'
                ]
            ])
            ->getForm();
        return $this->render('homePrincipale/searchBar.html.twig',[
            'searchForm'=>$form->createView()
        ]);

    }
    /**
     * @Route("/Search", name="Search")
     * @param Request $request
     */
    public function Search(Request $request, CategoryRepository $repo)
    {

        $formValues = $request->get('form');
        $query= $formValues['text'];

        if($query) {
            $Products= $repo->findGategoryByName(trim($query));

            return $this->render('homePrincipale/homePrincipale.html.twig', [
                'categories' => $Products
            ]);
        }
    }

    #[Route('/update/status/{id}/{status}', name: 'update_category_status')]
    public function updateStatus(Category $category, $status): Response
    {
        $category->setStatus($status);
        $this->entityManager->persist($category);

        // actually executes the queries (i.e. the INSERT query)
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Category status updated successfully!'
        );

        return $this->redirectToRoute('category.index');
    }




}