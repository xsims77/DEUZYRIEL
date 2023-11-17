<?php

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Form\OrganizationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrganizationController extends AbstractController
{
    #[Route('/organization', name: 'organization.index', methods:['GET'])]
    public function index(OrganizationRepository $organizationRepository): Response
    {

        $organizations = $organizationRepository->findAll();

        return $this->render('pages/organization/index.html.twig', compact("organizations"));
    }
    
    #[Route('/organization/create', name: 'organization.create', methods:['GET', 'POST'])]
    public function organizationCreate(Request $request,EntityManagerInterface $em): Response
    {

        $organization = new Organization();
        $form = $this->createForm(OrganizationFormType::class, $organization);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

            $em->persist($organization);
            $em->flush();

            $this->addFlash("success", "l'entité ou le nom de la société a été ajoutée avec succès.");
            return $this->redirectToRoute("organization.index");
        }

        return $this->render('pages/organization/create.html.twig',[
            'organizationForm'    => $form->createView()
        ]);
    }

    #[Route('/organization/{id}/edit', name: 'organization.edit', methods:['GET','PUT'])]
    public function organizationEdit(Organization $organization, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(OrganizationFormType::class, $organization, [
            "method"    => "PUT"
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            
            $em->persist($organization);
            $em->flush();

            $this->addFlash("success", "l'entité ou le nom de la société a été modifié avec succès.");
            return $this->redirectToRoute('organization.index');
        }

        return $this->render('pages/organization/edit.html.twig', [
            'organizationForm'    => $form->createView(),
            'organization'      => $organization
        ]);
    }

    #[Route('/organization/{id}/delete', name: 'organization.delete', methods:['DELETE'])]
    public function organizationDelete(Organization $organization, Request $request, EntityManagerInterface $em) : Response
    {

        if($this->isCsrfTokenValid("organization_delete_". $organization->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($organization);
            $em->flush();

            $this->addFlash("success", "L'entité sélétionné a bien été supprimé");
        }

        return $this->redirectToRoute('organization.index');
    }
}
