<?php

namespace App\Admin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminPasskeyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('passkey', PasswordType::class, [
                'label' => 'Admin Passkey',
                'attr' => [
                    'placeholder' => 'Enter the admin passkey',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the admin passkey'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Become Admin',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }
}
