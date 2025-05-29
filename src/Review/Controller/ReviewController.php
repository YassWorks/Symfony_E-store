<?php
namespace App\Review\Controller;

use App\Review\Service\ReviewService;
use App\Review\Form\ReviewType;
use App\Entity\Product;
use App\Product\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/review')]
#[IsGranted('ROLE_BUYER')]
class ReviewController extends AbstractController
{
    private ReviewService $reviewService;
    private ProductService $productService;

    public function __construct(ReviewService $reviewService, ProductService $productService)
    {
        $this->reviewService = $reviewService;
        $this->productService = $productService;
    }

    #[Route('/{id}', name: 'product_reviews', methods: ['GET'])]
    public function list(int $id): Response
    {
        $reviews = $this->reviewService->getReviewsForProduct($id);
        return $this->render('review/list.html.twig', [
            'productId' => $id,
            'reviews'   => $reviews,
        ]);
    }

    #[Route('/new/{id}', name: 'product_review', methods: ['GET', 'POST'])]
    public function add(int $id, Request $request): Response
    {
        $product = $this->productService->get($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }
        $existingReview = $this->reviewService->findReviewByUserAndProduct(
            $this->getUser(),
            $product
        );

        $form = $this->createForm(ReviewType::class, $existingReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewService->saveReview(
                $this->getUser(),
                $product,
                $form->get('rating')->getData(),
                $form->get('comment')->getData()
            );

            return $this->redirectToRoute('product_show', ['id' => $id]);
        }

        return $this->render('review/add.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
}
?>