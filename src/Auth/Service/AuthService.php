<?php

namespace App\Auth\Service;

use App\Auth\Entity\User;
use App\Shared\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function registerUser(User $user, FormInterface $form): void
    {
        // encode the password
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );
        
        // set default roles
        $user->addRole(Role::ROLE_BUYER);
        
        // save the User
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}