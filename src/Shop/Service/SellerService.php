<?php

namespace App\Shop\Service;

use App\Auth\Entity\User;
use App\Shop\Entity\Shop;
use App\Shared\Enum\Role;
use App\Shared\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SellerService
{    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileUploader $fileUploader
    ) {
    }

    public function registerShop(Shop $shop, FormInterface $form, User $user): bool
    {
        /** @var UploadedFile $logoFile */
        $logoFile = $form->get('logo')->getData();
          if ($logoFile) {
            $result = $this->fileUploader->uploadFile($logoFile);
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
}