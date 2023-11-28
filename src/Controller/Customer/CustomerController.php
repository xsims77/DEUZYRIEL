<?php

namespace App\Controller\Customer;

use App\Entity\Organization;
use App\Form\MoralFormType;
use App\Entity\MoralCustomers;
use App\Form\PhysicalFormType;
use App\Entity\PhysicalCustomers;
use App\Template\TemplateManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MoralCustomersRepository;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PhysicalCustomersRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends TemplateManager
{
    #[Route('/project/{id_project}/customer', name: 'project.customer.index', methods:['GET'])]
    public function index(Request $request, ProjectRepository $projectRepository) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);

        return $this->display($request, 'pages/customer/index.html.twig', [
            'project' => $projectEntity
        ]);
    }
	
    #[Route('/project/{id_project}/customer/physical', name: 'project.customer.physical', methods:['GET'])]
    public function physicalIndex(Request $request, PhysicalCustomersRepository $physicalCustomersRepository, ProjectRepository $projectRepository) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		// code identique avec moralIndex, on factorise le code
		$data = $this->getCustomers($physicalCustomersRepository, $request, $projectRepository);
		
		return $this->display($request, 'pages/customer/physical_index.html.twig', [
			'physicals'    => $data['customers'],
			'project'   => $data['project']
		]);
    }

    #[Route('/project/{id_project}/customer/moral', name: 'project.customer.moral', methods:['GET'])]
    public function moralIndex(Request $request, MoralCustomersRepository $moralCustomersRepository, ProjectRepository $projectRepository) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		// code identique avec physicalIndex, on factorise le code
        $data = $this->getCustomers($moralCustomersRepository, $request, $projectRepository);
		
        return $this->display($request, 'pages/customer/moral_index.html.twig', [
            'morals'    => $data['customers'],
            'project'   => $data['project']
        ]);
    }
	
	private function getCustomers($customerRepository, Request $request, ProjectRepository $projectRepository): RedirectResponse|array
	{
		// physical et moral ont le même code, la seule différence c'est le customerRepository qui change, du coup on ajuste le code pour matcher les 2 cas
		$data = ['project' => $projectRepository->findOneBy(['id' => $request->get('id_project')])];
		$activeRelation = $request->getSession()->get('active_relation');
		
		// on utilise une fonction static centrale qui permet de vérifier le role sans avoir à écrire la condition dans le code à chaque fois
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
			$data['customers'] = $customerRepository->findAll();
		} else {
			$data['customers'] = $customerRepository->findBy(['organization' => $activeRelation['organizationId']]);
		}
		
		return $data;
	}

    #[Route('/project/{id_project}/customer/create', name: 'project.customer.create', methods:['GET','POST'])]
    public function donorCreate(Request $request, EntityManagerInterface $em, OrganizationRepository $organizationRepository, ProjectRepository $projectRepository) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);
        $activeRelation = $request->getSession()->get('active_relation');
		
        $physicalEntity = new PhysicalCustomers();
        $physicalForm = $this->createForm(PhysicalFormType::class, $physicalEntity, [
			'role_name'   => $activeRelation['roleName']
        ]);
        $physicalForm->handleRequest($request);

        if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
            $organizationEntity = $physicalForm->get('organization')->getData();
        } else {
            $organizationEntity = $organizationRepository->findOneBy(['id' => $activeRelation['organizationId']]);
        }
		// on factorise le code de vérification du form
		$this->isDonorFormSubmitted($em, $physicalForm, $physicalEntity, $organizationEntity, 'project.customer.physical');

        $moralEntity = new MoralCustomers();
        $moralForm = $this->createForm(MoralFormType::class, $moralEntity, [
			'role_name'   => $activeRelation['roleName']
        ]);
        $moralForm->handleRequest($request);
		
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
            $organizationEntity = $physicalForm->get('organization')->getData();
        } else {
            $organizationEntity = $organizationRepository->findOneBy(['id' => $activeRelation['organizationId']]);
        }
		// on factorise le code de vérification du form
		$this->isDonorFormSubmitted($em, $moralForm, $moralEntity, $organizationEntity, 'project.customer.moral');
		
        return $this->display($request, 'pages/customer/create.html.twig',[
            'physicalForm'  => $physicalForm->createView(),
            'moralForm'     => $moralForm->createView(),
            'project'       => $projectEntity
        ]);
    }
	
	private function isDonorFormSubmitted(EntityManagerInterface $em, $form, $customerEntity, Organization $organizationEntity, string $route): void
	{
		// code identique pour le form physical ou moral, on ajuste seulement la route
		if ($form->isSubmitted() && $form->isValid()) {
			$form->setOrganization($organizationEntity);
			$em->persist($customerEntity);
			$em->flush();
			$this->addFlash("success", "Le nouveau donateur a bien été pris en compte.");
			
			$this->redirectToRoute($route);
		}
	}

    #[Route('/project/{id_project}/customer/{id_physical}/physicalEdit', name: 'project.customer.physicalEdit', methods:['GET','PUT'])]
    public function physicalEdit(PhysicalCustomersRepository $physicalCustomersRepository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);
        $physicalEntity = $physicalCustomersRepository->findOneBy(['id' => $request->get('id_physical')]);
        $activeRelation = $request->getSession()->get('active_relation');
        $form = $this->createForm(PhysicalFormType::class, $physicalEntity, [
            'method'    => 'PUT',
            'role_name'   => $activeRelation['roleName']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($physicalEntity);
            $em->flush();
            $this->addFlash("success", "Les modifications de ce donateur a bien été pris en compte.");
			
            return $this->redirectToRoute('project.customer.physical');
        }
		
        return $this->display($request, 'pages/customer/physicalEdit.html.twig', [
            'physicalForm'  => $form->createView(),
            'physical'      => $physicalEntity,
            'project'       => $projectEntity
        ]);
    }

    #[Route('/project/{id_project}/customer/{id_physical}/physicalDelete', name: 'project.customer.physicalDelete', methods:['DELETE'])]
    public function physicalDelete(Request $request, PhysicalCustomersRepository $physicalCustomersRepository, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $physicalEntity = $physicalCustomersRepository->findOneBy(['id' => $request->get('id_physical')]);
		// early return
		// on gère le cas où le token n'est pas valide, pour que ça renvoie une erreur (je sais pas si c'est géré auto par SF)
        if (!$this->isCsrfTokenValid("physical_delete_" . $physicalEntity->getId(), $request->request->get("csrf_token"))) {
            $this->addFlash("error", "Fail to submit form");
			
			return $this->redirectToRoute('project.customer.physical');
        }
		
		$em->remove($physicalEntity);
		$em->flush();
		$this->addFlash("success", "La suppression du donateur a bien été prise en compte.");
		
        return $this->redirectToRoute('project.customer.physical');
    }

    #[Route('/project/{id_project}/customer/{id_moral}/moralEdit', name: 'project.customer.moralEdit', methods:['GET','PUT'])]
    public function moralEdit(MoralCustomersRepository $moralCustomersRepository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);
        $moralEntity = $moralCustomersRepository->findOneBy(['id' => $request->get('id_moral')]);
        $activeRelation = $request->getSession()->get('active_relation');
        $form = $this->createForm(MoralFormType::class, $moralEntity, [
            'method'    => 'PUT',
			'role_name'   => $activeRelation['roleName']
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($moralEntity);
            $em->flush();
            $this->addFlash("success","Les modifications de ce donateur a bien été pris en compte.");
			
            return $this->redirectToRoute('project.customer.moral');
        }
		
        return $this->display($request, 'pages/customer/moralEdit.html.twig', [
            'moralForm' => $form->createView(),
            'moral'     => $moralEntity,
            'project'   => $projectEntity,
        ]);
    }

    #[Route('/project/{id_project}/customer/{id_moral}/moralDelete', name: 'project.customer.moralDelete', methods:['DELETE'])]
    public function moralDelete(MoralCustomersRepository $moralCustomersRepository, Request $request, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $moralEntity = $moralCustomersRepository->findOneBy(['id'=>$request->get('id_moral')]);
		// early return
		// on gère le cas où le token n'est pas valide, pour que ça renvoie une erreur (je sais pas si c'est géré auto par SF)
        if (!$this->isCsrfTokenValid("moral_delete_" . $moralEntity->getId(), $request->request->get("csrf_token"))) {
			$this->addFlash("error", "Fail to submit form");
			
			return $this->redirectToRoute('project.customer.moral');
        }
		
		$em->remove($moralEntity);
		$em->flush();
		$this->addFlash("success", "La suppression du donateur a bien été prise en compte.");
		
        return $this->redirectToRoute('project.customer.moral');
    }

}
