<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\MoralCustomers;
use App\Template\TemplateManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class MoralFormType extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('companyName', TextType::class)
			->add('companyType', TextType::class)
			->add('siret', TextType::class)
			->add('email', EmailType::class)
			->add('address', TextType::class)
			->add('zip', TextType::class)
			->add('city', TextType::class)
			->add('country', TextType::class);
		
		if (isset($options['role_name']) && TemplateManager::isRoleAdmin($options['role_name'])) {
			$builder
				->add('organization', EntityType::class,[
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
				]);
		}
	}
	
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => MoralCustomers::class,
			'role_name'  => null,
		]);
	}
}
