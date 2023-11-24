<?php

namespace App\Controller\Customer;

use App\Form\MoralFormType;
use App\Entity\MoralCustomers;
use App\Form\PhysicalFormType;
use App\Entity\PhysicalCustomers;
use App\Template\TemplateManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MoralCustomersRepository;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PhysicalCustomersRepository;
use App\Repository\ProjectRepository;
use App\Repository\RolesRepository;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends TemplateManager
{
    #[Route('/project/{id_project}/customer', name: 'project.customer.index', methods:['GET'])]
    public function index(Request $request, ProjectRepository $projectRepository) : Response
    {

        $projectEntity = $projectRepository->findOneBy(['id'=>$request->get('id_project')]);

        return $this->display($request, 'pages/customer/index.html.twig', [
            'project' => $projectEntity
        ]);
    }

    //Route pour Donateur physique
    #[Route('/project/{id_project}/customer/physical', name: 'project.customer.physical', methods:['GET'])]
    public function physicalIndex(Request $request, RolesRepository $rolesRepository, PhysicalCustomersRepository $physicalCustomersRepository, ProjectRepository $projectRepository) : Response
    {
        $projectEntity = $projectRepository->findOneBy(['id'=>$request->get('id_project')]);

        $activeRelation = $request->getSession()->get('active_relation');
      
        if (empty($activeRelation)) {
            $this->addFlash("warning", "Un problème est survenue sur votre connexion, veuillez vous reconnecter.");
            return $this->redirectToRoute('app.logout');
        }

        $role = $rolesRepository->findOneBy(['id' => array_values($activeRelation)]);

        if ($role->getRoleName() == "ROLE_ADMIN")
        { 
            $physicals = $physicalCustomersRepository->findAll();
        }else{
            $physicals = $physicalCustomersRepository->findBy(['organization' => array_key_first($activeRelation)]);
        }

        return $this->display($request, 'pages/customer/physical_index.html.twig', [
            "physicals" => $physicals,
            "project"   => $projectEntity
        ]);
    }

    //Route pour Donateur moral
    #[Route('/project/{id_project}/customer/moral', name: 'project.customer.moral', methods:['GET'])]
    public function moralIndex(Request $request, RolesRepository $rolesRepository, MoralCustomersRepository $moralCustomersRepository, ProjectRepository $projectRepository) : Response
    {
        $projectUrl = $request->get('id_project');
        $projectId = $projectRepository->findOneBy(['id'=>$projectUrl]);

        $activeRelation = $request->getSession()->get('active_relation');
      

        if (empty($activeRelation)) {
            $this->addFlash("warning", "Un problème est survenue sur votre connexion, veuillez vous reconnecter.");
            return $this->redirectToRoute('app.logout');
        }

        $role = $rolesRepository->findOneBy(['id' => array_values($activeRelation)]);

        if ($role->getRoleName() == "ROLE_ADMIN")
        {
            $morals = $moralCustomersRepository->findAll();
        }else{
            $morals = $moralCustomersRepository->findBy(['organization' => array_key_first($activeRelation)]);
        }
        return $this->display($request, 'pages/customer/moral_index.html.twig', [
            'morals'    => $morals,
            'project'   => $projectId
        ]);
    }

    #[Route('/project/{id_project}/customer/create', name: 'project.customer.create', methods:['GET','POST'])]
    public function donorCreate(Request $request, EntityManagerInterface $em, OrganizationRepository $organizationRepository, ProjectRepository $projectRepository) : Response
    {
        $projectUrl = $request->get('id_project');
        $projectId = $projectRepository->findOneBy(['id'=>$projectUrl]);

        $activeRelation = $request->getSession()->get('active_relation');
        $activeOrganizationId = array_key_first($activeRelation);
        $activeRoleId = array_values($activeRelation)[0];

        $physical = new PhysicalCustomers();

        $form = $this->createForm(PhysicalFormType::class, $physical, [
            'role_id'   => $activeRoleId
        ]);
        $form->handleRequest($request);

        if ($activeRoleId == 1) {
            $organizationEntity = $form->get('organization')->getData();            
        }else{
            $organizationEntity = $organizationRepository->findOneBy(['id' => $activeOrganizationId]);
        }
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $physical->setOrganization($organizationEntity);
            $em->persist($physical);
            $em->flush();
            
            $this->addFlash("success", "Le nouveau donateur a bien été pris en compte.");
            return $this->redirectToRoute('customer.physical');
        }

        $moral = new MoralCustomers();
        $form1 = $this->createForm(MoralFormType::class, $moral, [
            'role_id'   => $activeRoleId
        ]);
        $form1->handleRequest($request);

        if ($activeRoleId == 1) {
            $organizationEntity = $form->get('organization')->getData();            
        }else{
            $organizationEntity = $organizationRepository->findOneBy(['id' => $activeOrganizationId]);
        }
        
        if($form1->isSubmitted() && $form1->isValid())
        {
            $moral->setOrganization($organizationEntity);
            $em->persist($moral);
            $em->flush();

            $this->addFlash("success", "Le nouveau donateur a bien été pris en compte.");
            return $this->redirectToRoute('project.customer.moral');
        }
        return $this->display($request, 'pages/customer/create.html.twig',[
            'physicalForm'  => $form->createView(),
            'moralForm'     => $form1->createView(),
            'project'       => $projectId
        ]);
    }

    #[Route('/project/{id_project}/customer/{id_physical}/physicalEdit', name: 'project.customer.physicalEdit', methods:['GET','PUT'])]
    public function physicalEdit(PhysicalCustomersRepository $physicalCustomersRepository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $em) : Response
    {

        $projectEntity = $projectRepository->findOneBy(['id'=>$request->get('id_project')]);
        $physical = $physicalCustomersRepository->findOneBy(['id'=>$request->get('id_physical')]);

        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = array_values($activeRelation)[0];

        $form = $this->createForm(PhysicalFormType::class, $physical, [
            'method'    => 'PUT',
            'role_id'   => $activeRoleId
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {

            $em->persist($physical);
            $em->flush();

            $this->addFlash("success", "Les modifications de ce donateur a bien été pris en compte.");
            return $this->redirectToRoute('project.customer.physical');
        }
        return $this->display($request, 'pages/customer/physicalEdit.html.twig', [
            'physicalForm'  => $form->createView(),
            'physical'      => $physical,
            'project'       => $projectEntity
        ]);
    }

    #[Route('/project/{id_project}/customer/{id_physical}/physicalDelete', name: 'project.customer.physicalDelete', methods:['DELETE'])]
    public function physicalDelete(Request $request, PhysicalCustomersRepository $physicalCustomersRepository, EntityManagerInterface $em) : Response
    {
        $physical = $physicalCustomersRepository->findOneBy(['id'=>$request->get('id_physical')]);

        if ( $this->isCsrfTokenValid("physical_delete_".$physical->getId(), $request->request->get("csrf_token") ) ) 
        {
            $em->remove($physical);
            $em->flush();

            $this->addFlash("success", "La suppression du donateur a bien été prise en compte.");
        }
        return $this->redirectToRoute('project.customer.physical');
    }

    #[Route('/project/{id_project}/customer/{id_moral}/moralEdit', name: 'project.customer.moralEdit', methods:['GET','PUT'])]
    public function moralEdit(MoralCustomersRepository $moralCustomersRepository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $em) : Response
    {

        $projectEntity = $projectRepository->findOneBy(['id'=>$request->get('id_project')]);
        $moral = $moralCustomersRepository->findOneBy(['id'=>$request->get('id_moral')]);
 
        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = array_values($activeRelation)[0];

        $form = $this->createForm(MoralFormType::class, $moral, [
            'method'    => 'PUT',
            'role_id'   => $activeRoleId
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {

            $em->persist($moral);
            $em->flush();

            $this->addFlash("success","Les modifications de ce donateur a bien été pris en compte.");
            return $this->redirectToRoute('project.customer.moral');
        }
        return $this->display($request, 'pages/customer/moralEdit.html.twig', [
            'moralForm' => $form->createView(),
            'moral'     => $moral,
            'project'   => $projectEntity,
        ]);
    }

    #[Route('/project/{id_project}/customer/{id_moral}/moralDelete', name: 'project.customer.moralDelete', methods:['DELETE'])]
    public function moralDelete(MoralCustomersRepository $moralCustomersRepository, Request $request, EntityManagerInterface $em) : Response
    {

        $moral = $moralCustomersRepository->findOneBy(['id'=>$request->get('id_moral')]);

        if ( $this->isCsrfTokenValid("moral_delete_".$moral->getId(), $request->request->get("csrf_token")) ) 
        {
            $em->remove($moral);
            $em->flush();

            $this->addFlash("success", "La suppression du donateur a bien été prise en compte.");
        }
        return $this->redirectToRoute('project.customer.moral');
    }

}
