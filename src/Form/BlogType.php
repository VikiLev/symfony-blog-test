<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\DataTransformer\TagTransformer;

class BlogType extends AbstractType
{
    public function __construct(
        private readonly TagTransformer $transformer,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'myclass'
                ]
            ])
            ->add('text', TextareaType::class, [
                'required' => true,
            ]);

            $builder->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')->orderBy('p.name', 'ASC');
                },
                'required' => false,
                'empty_data' => '',
                'placeholder' => '-- select category --',
                'choice_label' => 'name',
            ]);

        $builder->add('tags', TextType::class, array(
            'label' => 'Теги',
            'required' => false,
        ))
        ;

        $builder->get('tags')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
