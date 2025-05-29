<?php
namespace App\Wishlist\Controller;

use App\Wishlist\Entity\Wishlist;
use App\Wishlist\Service\WishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Product\Entity\Product;
use App\Product\Service\ProductService;


#[Route('/wishlist')]
#[IsGranted('ROLE_BUYER')]
class WishlistController extends AbstractController
{
    public function __construct(
        private readonly WishlistService $service,
        ProductService $productService
        ) {}

    #[Route('', name: 'wishlist_index', methods: ['GET'])]
    public function index(): Response
    {
        $wishlist = $this->service->getOrCreateByUser($this->getUser());
        return $this->render('wishlist/index.html.twig', ['wishlist'=>$wishlist]);
    
    }    #[Route('/add/{id}', name:'wishlist_add', methods:['POST','GET'])]
    public function add(Request $request, Product $product): Response
    {
        $this->service->addProduct($this->getUser(), $product);
        
        // Check if this is an AJAX request
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => sprintf('%s added to wishlist!', $product->getTitle()),
                'action' => 'added'
            ]);
        }
        
        return $this->redirectToRoute('wishlist_index');
    }    #[Route('/remove/{id}', name: 'wishlist_delete', methods: ['GET', 'POST'])]
    public function remove(Request $request, Product $product): Response
    {
        $this->service->removeProduct($this->getUser(), $product);
        
        // Check if this is an AJAX request
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => sprintf('%s removed from wishlist!', $product->getTitle()),
                'action' => 'removed'
            ]);
        }
        
        return $this->redirectToRoute('wishlist_index');
    }

}
?>