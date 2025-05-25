<?php

namespace App\Auth\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'label' => 'Email Address'
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'Password'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sign In',
                'attr' => ['class' => 'btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
