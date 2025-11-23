<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('text')

            ->add('category', EntityType::class, [
                 'class' => Category::class,
                 'query_builder' => function ($repository) {
                     return $repository->createQueryBuilder('p')->orderBy('p.name', 'ASC');
                 },
                 'required' => false,
                 'empty_data' => '',
                 'placeholder' => '-- выбор категории --',
                'choice_label' => 'name',
             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
