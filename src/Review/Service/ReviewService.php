<?php
// src/Review/Service/ReviewService.php
namespace App\Review\Service;

use App\Review\Entity\Review;
use App\Review\Repository\ReviewRepository;
use App\Product\Entity\Product;
use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ReviewService
{
    private $em;
    private $repo;

    public function __construct(EntityManagerInterface $em, ReviewRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }

    public function findReviewByUserAndProduct(User $user, Product $product): ?Review
    {
        return $this->repo->findOneBy([
            'user' => $user,
            'product' => $product
        ]);
    }

    public function saveReview(User $user, Product $product, int $rating, ?string $comment): Review
    {

        // check s’il existait déjà une note du même user pour ce produit
        $existing = $this->findReviewByUserAndProduct($user, $product);

        $review = $existing ?? new Review();
        $review
            ->setUser($user)
            ->setProduct($product)
            ->setRating($rating)
            ->setComment($comment);

        $this->em->persist($review);
        $this->em->flush();

        return $review;
    }

    /**
     * Liste des reviews d’un produit.
     *
     * @return Review[]
     */
    public function getReviewsForProduct(int $productId): array
    {
        return $this->repo->findByProduct($productId);
    }
    public function getAvgs(int $productId): ?float
    {
        return $this->repo->getAverageRatingForProduct($productId);
    }
    public function getProdsAvg(array $productIds): array
    {
        return $this->repo->getAverageRatingsForShopProducts($productIds);
    }
}
