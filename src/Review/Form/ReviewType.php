<?php

namespace App\Review\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Review\Entity\Review;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', IntegerType::class, [
                'label' => 'Note (1 à 5)',
                'attr'  => ['min' => 1, 'max' => 5],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label'    => 'Commentaire',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => Review::class,
            'csrf_protection' => true,
        ]);
    }
}
?>