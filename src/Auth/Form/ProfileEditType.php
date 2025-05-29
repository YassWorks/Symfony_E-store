<?php

namespace App\Auth\Form;

use App\Auth\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full Name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your full name.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Your name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Your name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email address.',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Update Profile',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
