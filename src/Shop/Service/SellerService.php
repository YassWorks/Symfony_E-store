<?php

namespace App\Shop\Service;

use App\Auth\Entity\User;
use App\Shop\Entity\Shop;
use App\Shared\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class SellerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private string $shopLogosDirectory
    ) {
    }

    public function registerShop(Shop $shop, FormInterface $form, User $user): bool
    {
        // Process logo upload
        /** @var UploadedFile $logoFile */
        $logoFile = $form->get('logo')->getData();
        
        if ($logoFile) {
            $result = $this->uploadLogo($logoFile);
            if (!$result['success']) {
                return false;
            }
            $shop->setLogoUrl('/uploads/shop_logos/' . $result['filename']);
        }
        
        // Process categories
        $selectedCategories = $form->get('categories')->getData();
        foreach ($selectedCategories as $category) {
            $shop->addCategory($category);
        }
        
        // Save the shop
        $this->entityManager->persist($shop);
        $this->entityManager->flush();
        
        // Update user roles
        if ($user) {
            $user->addRole(Role::ROLE_SELLER);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        
        return true;
    }

    private function uploadLogo(UploadedFile $logoFile): array
    {
        $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();
        
        try {
            $logoFile->move($this->shopLogosDirectory, $newFilename);
            return [
                'success' => true,
                'filename' => $newFilename
            ];
        } catch (FileException) {
            return [
                'success' => false,
                'error' => 'There was a problem uploading your logo. Please try again.'
            ];
        }
    }
}