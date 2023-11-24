<?php

namespace App\Form;

use App\Entity\Roles;
use App\Entity\Relation;
use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', EntityType::class,[
                'mapped'        => false,
                'required'      => true,
                'class'         => Roles::class,
                'choice_label'  => 'roleName',
                'placeholder'   => 'Sélectionnez un rôle',
                'constraints'   => [
                    new NotBlank([
                        'message'   => 'Veuillez choisir un rôle.'
                    ])
                ]
            ])
            ->add('organization', EntityType::class, [
                'mapped'        => false,
                'required'      => true,
                'class'         => Organization::class,
                'choice_label'  => 'organizationName',
                'placeholder'   => 'Sélectionnez une organisation',
                'constraints'   => [
                    new NotBlank([
                        'message'   => 'Veuillez choisir une organisation avant de valider.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Relation::class,
        ]);
    }
}
