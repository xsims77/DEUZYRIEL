<?php

namespace App\Controller\User;

use App\Entity\Users;
use App\Entity\Relation;
use App\Form\UserFormType;
use App\Form\RelationFormType;
use App\Template\TemplateManager;
use App\Repository\RolesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends TemplateManager
{
	#[Route('/users', name: 'users.index', methods:['GET'])]
	public function index(UsersRepository $usersRepository, Request $request, RolesRepository $rolesRepository): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$activeRelation = $request->getSession()->get('active_relation');
		if (TemplateManager::isRoleAgent($activeRelation['roleName'])) {
			return $this->redirectToRoute('welcome.index');
		}
		
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
			$userEntity = $usersRepository->findAll();
		} else {
			$userEntity = $usersRepository->findAllByOrganization($activeRelation['organizationId']);
		}
		$roleEntity = $rolesRepository->findOneBy(['id' => $activeRelation['roleId']]);
		
		return $this->display($request, 'pages/users/index.html.twig', [
			'users' => $userEntity,
			'role'  => $roleEntity
		]);
	}
	
	#[Route('/users/create', name: 'users.create', methods:['GET','POST'])]
	public function create(Request $request,  UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, OrganizationRepository $organizationRepository, RolesRepository $rolesRepository): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		if (count($organizationRepository->findAllNonAdmin()) == 0) {
			$this->addFlash("warning", "Vous devez créer au moins une organisation pour accéder au formulaire d'un nouvel utilisateur.");
			
			return $this->redirectToRoute('organization.index');
		}
		
		$activeRelation = $request->getSession()->get('active_relation');
		$userEntity = new Users();
		$form = $this->createForm(UserFormType::class, $userEntity, ['role_name' => $activeRelation['roleName']]);
		$form->handleRequest($request);
		
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
			$roleEntity = $form->get('roleName')->getData();
			$organizationEntity = $form->get('organizationName')->getData();
		} else {
			$roleEntity = $rolesRepository->findOneBy(['roleName' => 'ROLE_AGENT']);
			$organizationEntity = $organizationRepository->findOneBy(['id' => $activeRelation['organizationId']]);
		}
		
		if ($form->isSubmitted() && $form->isValid()) {
			$userEntity->setPassword(
				$userPasswordHasher->hashPassword(
					$userEntity,
					$form->get('password')->getData()
				)
			);
			$em->persist($userEntity);
			$em->flush();
			
			$relationEntity = new Relation();
			$relationEntity->setUser($userEntity);
			$relationEntity->setRole($roleEntity);
			$relationEntity->setOrganization($organizationEntity);
			$em->persist($relationEntity);
			$em->flush();
			$this->addFlash("success", "le nouvel utilisateur a été ajoutée avec succès.");
			
			return $this->redirectToRoute("users.index");
		}
		
		return $this->display($request, 'pages/users/create.html.twig', [
			'form'  => $form->createView()
		]);
		
	}
	
	#[Route('/users/{id}/edit', name: 'users.edit', methods:['GET','PUT'])]
	public function edit(Users $userEntity, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$activeRelation = $request->getSession()->get('active_relation');
		$form = $this->createForm(UserFormType::class, $userEntity, [
			'role_name' => $activeRelation['roleName'],
			'method'    => 'PUT'
		]);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$userEntity->setPassword(
				$userPasswordHasher->hashPassword(
					$userEntity,
					$form->get('password')->getData()
				)
			);
			
			$em->persist($userEntity);
			$em->flush();
			$this->addFlash("success", "L'utilisateur a bien été modifié");
			
			return $this->redirectToRoute("users.index");
		}
		
		return $this->display($request, 'pages/users/edit.html.twig',[
			"userEditForm"  => $form->createView(),
			"user"          => $userEntity
		]);
	}
	
	#[Route('/users/{id}/delete', name: 'users.delete', methods:['DELETE'])]
	public function delete(Users $userEntity, Request $request, EntityManagerInterface $em) : Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		if (!$this->isCsrfTokenValid("users_delete_" . $userEntity->getId(), $request->request->get('csrf_token'))) {
			$this->addFlash("warning", "Un problème est survennue lors de la suppression.");
			
			return $this->redirectToRoute('users.index');
		}
		
		$em->remove($userEntity);
		$em->flush();
		$this->addFlash("success", "L'utilisateur sélétionné a bien été supprimé");
		
		return $this->redirectToRoute('users.index');
	}
	
	#[Route('/users/relation/{id}', name: 'users.relation.create', methods:['GET', 'POST'])]
	public function relationCreate(Request $request, EntityManagerInterface $em, UsersRepository $usersRepository, RolesRepository $rolesRepository, OrganizationRepository $organizationRepository) : Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$relationEntity = new Relation();
		$form = $this->createForm(RelationFormType::class, $relationEntity);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$relationEntity->setUser($usersRepository->findOneBy(['id' => $request->get('id')]));
			$relationEntity->setRole($rolesRepository->findOneBy(['id' => $form->get('role')->getData()]));
			$relationEntity->setOrganization($organizationRepository->findOneBy(['id' => $form->get('organization')->getData()]));
			$em->persist($relationEntity);
			$em->flush();
			
			$this->addFlash('success', 'La relation de l\'utilisateur a bien été pris en compte.');
			return $this->redirectToRoute('users.index');
		}
		
		return $this->display($request, 'pages/users/relation/create.html.twig', [
			'form'  => $form,
		]);
	}
}
