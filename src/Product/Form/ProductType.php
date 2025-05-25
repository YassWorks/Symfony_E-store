<?php
namespace App\Product\Form;

use App\Product\Entity\Product;
use App\Shared\Enum\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control mb-3']])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control mb-3',
                    'rows' => 4,
                    'style' => 'resize: none;'
                ]
            ])
            ->add('price', MoneyType::class, ['currency' => 'USD', 'attr' => ['class' => 'form-control mb-3']])
            ->add('stockQuantity', IntegerType::class, ['attr' => ['class' => 'form-control mb-3']])
            ->add('category', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn(Category $c) => ucwords(strtolower($c->value)), Category::cases()),
                    Category::cases()
                ),
                'attr' => ['class' => 'form-select mb-3'],
            ])
            ->add('images', FileType::class, [
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['class' => 'form-control mb-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}