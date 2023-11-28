<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Form\ProjectFormType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use App\Repository\RolesRepository;
use App\Template\TemplateManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends TemplateManager
{
	#[Route('/project', name: 'project.index', methods:['GET'])]
	public function project(ProjectRepository $projectRepository, Request $request, RolesRepository $rolesRepository): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$activeRelation = $request->getSession()->get('active_relation');
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
			$projectEntity = $projectRepository->findAll();
		} else {
			$projectEntity = $projectRepository->findBy(['organization' => $activeRelation['organizationId']]);
		}
		$roleEntity = $rolesRepository->findOneBy(['id' => $activeRelation['roleId']]);
		
		return $this->display($request, 'pages/project/index.html.twig', [
			"projects"  => $projectEntity,
			"role"      => $roleEntity
		]);
	}
	
	#[Route('/project/create', name: 'project.create', methods:['GET', 'POST'])]
	public function projectCreate(Request $request, EntityManagerInterface $em, OrganizationRepository $organizationRepository) : Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		if (count($organizationRepository->findAll()) == 0) {
			$this->addFlash("warning", "Vous devez créer au moins une organisation avant de créer un projet.");
			
			return $this->redirectToRoute('organization.index');
		}
		
		$activeRelation = $request->getSession()->get('active_relation');
		$projectEntity = new Project();
		$organizationEntity = $organizationRepository->findBy(['id' => $activeRelation['organizationId']]);
		$form = $this->createForm(ProjectFormType::class, $projectEntity, ['organizations' => $organizationEntity]);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$projectEntity->setOrganization($form->get('organization')->getData());
			$em->persist($projectEntity);
			$em->flush();
			$this->addFlash("success", "Le projet a bien été validé");
			
			return $this->redirectToRoute('project.index');
		}
		
		return $this->display($request, 'pages/project/create.html.twig', [
			'projectForm'   => $form->createView()
		]);
	}
	
	#[Route('/project/{id}/edit', name: 'project.edit', methods:['GET', 'PUT'])]
	public function projectEdit(Project $projectEntity, Request $request, EntityManagerInterface $em): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$form = $this->createForm(ProjectFormType::class, $projectEntity, [
			"method"    => "PUT"
		]);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid())  {
			$projectEntity->setOrganization($form->get('organizationName')->getData());
			$em->persist($projectEntity);
			$em->flush();
			$this->addFlash("success", "Les modifications du projets ont été prises en compte.");
			
			return $this->redirectToRoute('project.index');
		}
		
		return $this->display($request, 'pages/project/edit.html.twig', [
			"projectForm"   => $form->createView(),
			"project"       => $projectEntity
		]);
	}
	
	#[Route('/project/{id}/show', name: 'project.show', methods:['GET'])]
	public function projectShow(Request $request, Project $project, OrganizationRepository $organizationRepository): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		if (count($organizationRepository->findAll()) == 0) {
			$this->addFlash("warning", "Vous devez créer au moin une entitée ou le nom de la société avant créer un projet.");
			
			return $this->redirectToRoute('admin.organization');
		}
		
		return $this->display($request, "pages/project/show.html.twig", compact("project"));
	}
	
	#[Route('/project/{id}/delete', name: 'project.delete', methods:['DELETE'])]
	public function projectDelete(Project $projectEntity, Request $request, EntityManagerInterface $em): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		if (!$this->isCsrfTokenValid("project_delete_" . $projectEntity->getId(), $request->request->get("csrf_token"))) {
			$this->addFlash("error", "Fail to submit form");
			
			return $this->redirectToRoute('project.index');
		}
		
		$em->remove($projectEntity);
		$em->flush();
		$this->addFlash("success", "La suppression du projet a bien été prise en compte.");
		
		return $this->redirectToRoute('project.index');
	}
	
}
