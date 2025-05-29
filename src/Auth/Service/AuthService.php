<?php

namespace App\Auth\Service;

use App\Auth\Entity\User;
use App\Shared\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormInterface;
use App\MailModule\Service\MailServiceInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private MailServiceInterface $mailService
    ) {}

    public function registerUser(User $user, FormInterface $form): void
    {
        // encode the password using the stuff we configured in security.yaml
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );
        
        // set default roles
        $user->addRole(Role::ROLE_BUYER);
        
        $this->mailService->send(
            $user->getEmail(),
            'Welcome to Our Store!',
            'emails/registration.html.twig',
            [
                'user'=> $user
            ]

        );
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}