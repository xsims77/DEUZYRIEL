<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('projectName',  TextType::class)
            ->add('content',      TextareaType::class)
            ->add('organization', ChoiceType::class, [
                'choices' => $options['organizations'],
                'placeholder'   => 'Choisissez une organisation',
                'choice_label' => 'organizationName', // Propriété à afficher dans le champ de choix
                'choice_value' => 'id', // Propriété utilisée comme valeur de chaque option
                'multiple' => false,
                'expanded' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'organizations' => null,
        ]);
    }
}
