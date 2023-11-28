<?php

namespace App\Controller\Don;


use App\Entity\Donations;
use App\Entity\PhysicalCustomers;
use App\Entity\Project;
use App\Form\DonFormType;
use App\Form\DonEditFormType;
use App\Template\TemplateManager;
use App\Repository\ProjectRepository;
use App\Repository\DonationsRepository;
use App\Repository\MoralCustomersRepository;
use App\Repository\PhysicalCustomersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DonController extends TemplateManager
{
    #[Route('project/{id_project}/don', name: 'project.don.index', methods:['GET'])]
    public function index(Request $request, DonationsRepository $donationsRepository, ProjectRepository $projectRepository) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $activeRelation = $request->getSession()->get('active_relation');
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);
     
        if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
            $donationsEntity = $donationsRepository->findAll();
        } else {
            $donationsEntity = $donationsRepository->findBy(['project' => $projectEntity->getId()]);
        }

        return $this->display($request, 'pages/don/index.html.twig', [
            'dons'      => $donationsEntity,
            'project'   => $projectEntity
        ]);
    }

    #[Route('project/{id_project}/customer/{id_physical}/don/physical_create', name: 'project.customer.don.physicalCreate', methods:['GET', 'POST'])]
    public function donPhysicalCreate(PhysicalCustomersRepository $physicalCustomersRepository, Request $request, ProjectRepository $projectRepository, EntityManagerInterface $em ) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $activeRelation = $request->getSession()->get('active_relation');
        $physicalEntity = $physicalCustomersRepository->findOneBy(['id' => $request->get('id_physical')]);

        $donationsEntity = new Donations;
        $form = $this->createForm(DonFormType::class, $donationsEntity, [
            'role_name'   => $activeRelation['roleName']
        ]);
        $form->handleRequest($request);
        
        if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
            $projectEntity = $form->get('project')->getData();
        } else {
            $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]) ;
        }
		$this->isDonorFormSubmitted($em, $form, $projectEntity, $physicalEntity, $donationsEntity);

        return $this->display($request, 'pages/don/physicalCreateDon.html.twig', [
            'form'      => $form->createView(),
            'physical'  => $physicalEntity,
            'project'   => $projectEntity
        ]);
    }

    #[Route('project/id_project/customer/{id_moral}/don/moral_create', name: 'project.customer.don.moralCreate', methods:['GET', 'POST'])]
    public function donMoralCreate(MoralCustomersRepository $moralCustomersRepository, Request $request, ProjectRepository $projectRepository, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $activeRelation = $request->getSession()->get('active_relation');
		$moralEntity = $moralCustomersRepository->findOneBy(['id'=> $request->get('id_moral')]);
		
        $donationsEntity = new Donations;
        $form = $this->createForm(DonFormType::class, $donationsEntity, [
            'role_name'   => $activeRelation['roleName'],
        ]);
        $form->handleRequest($request);
		
		if (TemplateManager::isRoleAdmin($activeRelation['roleName'])) {
            $projectEntity = $form->get('project')->getData();
        } else {
            $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]) ;
        }
		$this->isDonorFormSubmitted($em, $form, $projectEntity, $moralEntity, $donationsEntity);

        return $this->display($request, 'pages/don/moralCreateDon.html.twig', [
            'form'      => $form->createView(),
            'moral'     => $moralEntity,
            'project'   => $projectEntity
        ]);
    }
	
	private function isDonorFormSubmitted(EntityManagerInterface $em, $form, Project $projectEntity, $customerEntity, Donations $donationsEntity): void
	{
		if ($form->isSubmitted() && $form->isValid()) {
			$donationsEntity->setProject($projectEntity);
			if ($customerEntity instanceof PhysicalCustomers) {
				$donationsEntity->setPhysicalCustomer($customerEntity);
			} else {
				$donationsEntity->setMoralCustomer($customerEntity);
			}
			
			$em->persist($donationsEntity);
			$em->flush();
			$this->addFlash("success","Le don a bien été ajouter" );
			
			$this->redirectToRoute('don.index');
		}
	}

    #[Route('/don/{id}/edit', name: 'don.edit', methods:['GET', 'PUT'])]
    public function donEdit(Donations $donationsEntity, Request $request, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $form = $this->createForm(DonEditFormType::class, $donationsEntity, [
            "method"    => "PUT"
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($donationsEntity);
            $em->flush();
            $this->addFlash("success","Le don a bien été modifier" );
			
            return $this->redirectToRoute('don.index');
        }

        return $this->display($request, 'pages/don/edit.html.twig', [
            'form'  => $form
        ]);
    }

    #[Route('don/{id}/delete', name: 'don.delete', methods:['DELETE'])]
    public function donDelete(Donations $donationsEntity, Request $request, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        if (!$this->isCsrfTokenValid("don_delete_".$donationsEntity->getId(), $request->request->get('csrf_token')) ) {
			$this->addFlash("error", "Fail to submit form");
			
			return $this->redirectToRoute('don.index');
        }
		
		$em->remove($donationsEntity);
		$em->flush();
		$this->addFlash("success", "Le don a été supprimé avec succès");
		
        return $this->redirectToRoute('don.index');
    }
}
