<?php

namespace App\Form;

use App\Entity\Roles;
use App\Entity\Users;
use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserFormType extends AbstractType
{
    private $user;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être identique.',
                'required' => true,
            ])
            ->add('organizationName', EntityType::class,[
                'mapped'        => false,
                'required'      => true,
                'class'         => Organization::class,
                'choice_label'  => 'organizationName',
                'placeholder'   => 'Sélectionnez une organisation',            
            ])               
            ->add('roleName', EntityType::class, [
                'mapped'        => false,
                'required'      => true,
                'class'         => Roles::class,
                'choice_label'  => 'roleName',
                'placeholder'   => 'Sélectionnez un rôle',
                'constraints' => [
                    new NotBlank([
                      'message' => 'Veuillez choisir le rôle de l\'utilisateur.',
                    ])
                  ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Veuillez lire et accepter les conditions générale d\'utilisation.',
                    ]),
                ],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
