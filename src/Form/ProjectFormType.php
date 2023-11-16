<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
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
            ->add('organizationName', EntityType::class, [
                'mapped'         => false,
                'required'       => true,
                'class'          => Organization::class,
                'choice_label'   => 'organizationName',
                'placeholder'    => 'Choisir une entitée',
                'constraints' => [
                    new NotBlank([
                      'message'  => 'Veuillez choisir une entitée parmis la liste proposée.',
                    ])
                  ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
