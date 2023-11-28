<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Donations;
use App\Template\TemplateManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonFormType extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('donation_amount', NumberType::class)
			->add('donation_currency', TextType::class);
		
		if (isset($options['role_name']) && TemplateManager::isRoleAdmin($options['role_name'])) {
			$builder
				->add('project', EntityType::class, [
					'mapped'    => false,
					'required'  => true,
					'class'     => Project::class,
					'choice_label'  => 'projectName',
					'placeholder'   => 'Sélectionnez un projet',
					'constraints'   => [
						new NotBlank([
							'message'   => 'Veuillez choisir un projet parmis la liste proposée.',
						])
					]
				]);
		}
		
	}
	
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Donations::class,
			'role_name'  => null,
		]);
	}
}
