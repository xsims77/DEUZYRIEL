<?php

namespace App\Form;



use App\Entity\Users;
use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class AgentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options, ): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être identique.',
                'required' => true,
            ])
            // ->add('roleName', EntityType::class, [
            //     'mapped'        => false,
            //     'class'         => Roles::class,
            //     'choice_label'  => 'roleName',
            //     'query_builder' => function (RolesRepository $er): QueryBuilder {
            //         return $er->createQueryBuilder('u')
            //             ->where('u.roleName = :val')
            //             ->setParameter('val', 'Gestionnaire');
            //     },                
            // ])
            ->add('organizationName', EntityType::class,[
                'mapped'        => false,
                'required'      => true,
                'class'         => Organization::class,
                'choice_label'  => 'organizationName',
                'placeholder'   => 'Sélectionnez une entitée',
                'constraints'   => [
                    new NotBlank([
                      'message' => 'Veuillez choisir une entitée parmis la liste proposée.',
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
