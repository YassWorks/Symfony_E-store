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
{    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Product Title',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product title...']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Describe your product...'
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'USD',
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00']
            ])
            ->add('stockQuantity', IntegerType::class, [
                'label' => 'Stock Quantity',
                'attr' => ['class' => 'form-control', 'placeholder' => '0', 'min' => '0']
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => array_combine(
                    array_map(fn(Category $c) => ucwords(strtolower($c->value)), Category::cases()),
                    Category::cases()
                ),
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Select a category...'
            ])            ->add('images', FileType::class, [
                'label' => 'Product Images',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['class' => 'form-control', 'accept' => 'image/*']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}