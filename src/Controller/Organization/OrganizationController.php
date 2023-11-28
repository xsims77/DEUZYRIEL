<?php

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Template\TemplateManager;
use App\Form\OrganizationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrganizationController extends TemplateManager
{
	#[Route('/organization', name: 'organization.index', methods: ['GET'])]
	public function index(Request $request, OrganizationRepository $organizationRepository): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$activeRelation = $request->getSession()->get('active_relation');
		if (TemplateManager::isRoleAgent($activeRelation['roleName'])) {
			return $this->redirectToRoute('welcome.index');
		}
		
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
			$organizationEntity = $organizationRepository->findAllNonAdmin();
		} else {
			$organizationEntity = $organizationRepository->findBy(['id' => $activeRelation['organizationId']]);
		}
		return $this->display($request, 'pages/organization/index.html.twig', [
			'organizations' => $organizationEntity
		]);
	}
	
	#[Route('/organization/create', name: 'organization.create', methods: ['GET', 'POST'])]
	public function organizationCreate(Request $request, EntityManagerInterface $em): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$organizationEntity = new Organization();
		$form = $this->createForm(OrganizationFormType::class, $organizationEntity);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($organizationEntity);
			$em->flush();
			$this->addFlash("success", "l'entité ou le nom de la société a été ajoutée avec succès.");
			
			return $this->redirectToRoute("organization.index");
		}
		
		return $this->display($request, 'pages/organization/create.html.twig', [
			'organizationForm'    => $form->createView(),
		]);
	}
	
	#[Route('/organization/{id}/edit', name: 'organization.edit', methods: ['GET', 'PUT'])]
	public function organizationEdit(Organization $organizationEntity, Request $request, EntityManagerInterface $em): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$form = $this->createForm(OrganizationFormType::class, $organizationEntity, [
			"method"    => "PUT"
		]);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($organizationEntity);
			$em->flush();
			
			$this->addFlash("success", "l'entité ou le nom de la société a été modifié avec succès.");
			return $this->redirectToRoute('organization.index');
		}
		
		return $this->display($request, 'pages/organization/edit.html.twig', [
			'organizationForm' => $form->createView(),
			'organization'     => $organizationEntity
		]);
	}
	
	#[Route('/organization/{id}/delete', name: 'organization.delete', methods: ['DELETE'])]
	public function organizationDelete(Organization $organizationEntity, Request $request, EntityManagerInterface $em): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		if (!$this->isCsrfTokenValid("organization_delete_" . $organizationEntity->getId(), $request->request->get('csrf_token'))) {
			$this->addFlash("error", "Fail to submit form");
			
			return $this->redirectToRoute('organization.index');
		}
		
		$em->remove($organizationEntity);
		$em->flush();
		$this->addFlash("success", "L'entité sélectionné a bien été supprimé");
		
		return $this->redirectToRoute('organization.index');
	}
}
