<?php

namespace App\Controller\Don;


use App\Entity\Donations;
use App\Form\DonFormType;
use App\Form\DonEditFormType;
use App\Template\TemplateManager;
use App\Repository\ProjectRepository;
use App\Repository\DonationsRepository;
use App\Repository\MoralCustomersRepository;
use App\Repository\OrganizationRepository;
use App\Repository\PhysicalCustomersRepository;
use App\Repository\RolesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DonController extends TemplateManager
{
    #[Route('project/{id_project}/don', name: 'project.don.index', methods:['GET'])]
    public function index(Request $request, DonationsRepository $donationsRepository, ProjectRepository $projectRepository, RolesRepository $rolesRepository) : Response
    {

        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = $rolesRepository->findOneBy(['id' => array_values($activeRelation)]);
        $dons = $donationsRepository->findAll();

        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);

        $projectId = $projectEntity->getId();
     
        if ( $activeRoleId->getRoleName() == "ROLE_ADMIN" ) {
            $dons = $donationsRepository->findAll();
        }else{
            $dons = $donationsRepository->findBy(['project' => $projectId]);
        }

        return $this->display($request, 'pages/don/index.html.twig', [
            'dons'      => $dons,
            'project'   => $projectEntity
        ]);
    }

    #[Route('project/{id_project}/customer/{id_physical}/don/physical_create', name: 'project.customer.don.physicalCreate', methods:['GET', 'POST'])]
    public function donPhysicalCreate(PhysicalCustomersRepository $physicalCustomersRepository, Request $request, ProjectRepository $projectRepository, EntityManagerInterface $em) : Response
    {
        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = array_values($activeRelation)[0];
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);
        $physical = $physicalCustomersRepository->findOneBy(['id' => $request->get('id_physical')]);

        $don = new Donations;
        
        $form = $this->createForm(DonFormType::class, $don, [
            'role_id'   => $activeRoleId
        ]);
        $form->handleRequest($request);
        
        if($activeRoleId == 1)
        {
            $projectId = $form->get('project')->getData();
        }else{
            $projectId = $projectRepository->findOneBy(['id' => $request->get('id')]) ;
        }

        if ($form->isSubmitted() && $form->isValid()) 
        {

            $don->setProject($projectId);
            $don->setPhysicalCustomer($physical);
            $em->persist($don);
            $em->flush();

            $this->addFlash("success","Le don a bien été ajouter" );
            return $this->redirectToRoute('don.index');
        }

        return $this->display($request, 'pages/don/physicalCreateDon.html.twig', [
            'form'      => $form->createView(),
            'physical'  => $physical,
            'project'   => $projectEntity
        ]);
    }

    #[Route('project/id_project/customer/{id_moral}/don/moral_create', name: 'project.customer.don.moralCreate', methods:['GET', 'POST'])]
    public function donMoralCreate( MoralCustomersRepository $moralCustomersRepository, Request $request, ProjectRepository $projectRepository, EntityManagerInterface $em) : Response
    {

        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = array_values($activeRelation)[0];
        $projectEntity = $projectRepository->findOneBy(['id' => $request->get('id_project')]);
        $moral = $moralCustomersRepository->findOneBy(['id'=> $request->get('id_moral')]);


        $don = new Donations;

        $form = $this->createForm(DonFormType::class, $don, [
            'role_id'   => $activeRoleId,
        ]);

        $form->handleRequest($request);

        if($activeRoleId == 1)
        {
            $projectId = $form->get('project')->getData();
        }else{
            $projectId = $projectRepository->findOneBy(['id' => $request->get('id')]) ;
        }

        if ($form->isSubmitted() && $form->isValid())
        {
            $don->setProject($projectId);
            $don->setMoralCustomer($moral);
            $em->persist($don);
            $em->flush();

            $this->addFlash("success","Le don a bien été ajouter" );
            return $this->redirectToRoute('don.index');

        }

        return $this->display($request, 'pages/don/moralCreateDon.html.twig', [
            'form'      => $form->createView(),
            'moral'     => $moral,
            'project'   => $projectEntity
        ]);
    }

    #[Route('/don/{id}/edit', name: 'don.edit', methods:['GET', 'PUT'])]
    public function donEdit(Donations $don, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(DonEditFormType::class, $don, [
            "method"    => "PUT"
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $em->persist($don);
            $em->flush();

            $this->addFlash("success","Le don a bien été modifier" );
            return $this->redirectToRoute('don.index');
        }

        return $this->display($request, 'pages/don/edit.html.twig', [
            'form'  => $form
        ]);
    }

    #[Route('don/{id}/delete', name: 'don.delete', methods:['DELETE'])]
    public function donDelete(Donations $don, Request $request, EntityManagerInterface $em) : Response
    {
        if ( $this->isCsrfTokenValid("don_delete_".$don->getId(), $request->request->get('csrf_token')) ) 
        {
            $em->remove($don);
            $em->flush();

            $this->addFlash("success", "Le don a été supprimé avec succès");
            
        }
        return $this->redirectToRoute('don.index');
    }
}
