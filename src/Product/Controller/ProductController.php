<?php
namespace App\Product\Controller;

use App\Product\Entity\Product;
use App\Product\Form\ProductType;
use App\Product\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/products')]
class ProductController extends AbstractController
{
    public function __construct(private readonly ProductService $service) {}

    #[Route('', name: 'product_index', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->service->list();
        return $this->render('products/index.html.twig', compact('products'));
    }

    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SELLER')]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($product);
            return $this->redirectToRoute('product_index');
        }

        return $this->render('products/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('products/show.html.twig', compact('product')); 
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SELLER')]
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($product);
            return $this->redirectToRoute('product_index');
        }
        return $this->render('products/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/{id}', name: 'product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SELLER')]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->service->delete($product);
        }
        return $this->redirectToRoute('product_index');
    }
}