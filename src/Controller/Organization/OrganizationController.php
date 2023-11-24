<?php

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Template\TemplateManager;
use App\Form\OrganizationFormType;
use App\Repository\RolesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrganizationController extends TemplateManager
{
    #[Route('/organization', name: 'organization.index', methods: ['GET'])]
    public function index(Request $request, OrganizationRepository $organizationRepository, RolesRepository $rolesRepository): Response
    {

        $activeRelation = $request->getSession()->get('active_relation');

        if (empty($activeRelation)) {
            // faire une erreur
            $this->addFlash("warning", "Un problème est survenue sur votre connexion, veuillez vous reconnecter.");
            // et rediriger vers welcome.index
            return $this->redirectToRoute('app.logout');
        }

        

        // on vérifie si le user est admin
        $role = $rolesRepository->findOneBy(['id' => array_values($activeRelation)]);
        if ($role->getRoleName() == "ROLE_AGENT") {
            return $this->redirectToRoute('welcome.index');
        }
        
        if ($role->getRoleName() == "ROLE_ADMIN") {
            $organizations = $organizationRepository->findAllNonAdmin();
        } else {
            $organizations = $organizationRepository->findBy(['id' => array_key_first($activeRelation)]);
        }
        return $this->display($request, 'pages/organization/index.html.twig', [
            'organizations' => $organizations
        ]);
    }

    #[Route('/organization/create', name: 'organization.create', methods: ['GET', 'POST'])]
    public function organizationCreate(Request $request, EntityManagerInterface $em): Response
    {
        $organization = new Organization();
        $form = $this->createForm(OrganizationFormType::class, $organization);
        $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {

        //     $em->persist($organization);
        //     $em->flush();

        //     $this->addFlash("success", "l'entité ou le nom de la société a été ajoutée avec succès.");
        //     return $this->redirectToRoute("organization.index");
        // }

        // return $this->display($request, 'pages/organization/create.html.twig', [
        //     'organizationForm'    => $form->createView(),
        // ]);

        // -----------------------
        // méthode early return

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->display($request, 'pages/organization/create.html.twig', [
                    'organizationForm'    => $form->createView(),
                ]);
            }

            $em->persist($organization);
            $em->flush();

            $this->addFlash("success", "l'entité ou le nom de la société a été ajoutée avec succès.");
            return $this->redirectToRoute("organization.index");
        }

        return $this->display($request, 'pages/organization/create.html.twig', [
            'organizationForm'    => $form->createView(),
        ]);
    }

    #[Route('/organization/{id}/edit', name: 'organization.edit', methods: ['GET', 'PUT'])]
    public function organizationEdit(Organization $organization, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(OrganizationFormType::class, $organization, [
            "method"    => "PUT"
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($organization);
            $em->flush();

            $this->addFlash("success", "l'entité ou le nom de la société a été modifié avec succès.");
            return $this->redirectToRoute('organization.index');
        }

        return $this->display($request, 'pages/organization/edit.html.twig', [
            'organizationForm'    => $form->createView(),
            'organization'      => $organization
        ]);
    }

    #[Route('/organization/{id}/delete', name: 'organization.delete', methods: ['DELETE'])]
    public function organizationDelete(Organization $organization, Request $request, EntityManagerInterface $em): Response
    {

        if ($this->isCsrfTokenValid("organization_delete_" . $organization->getId(), $request->request->get('csrf_token'))) {
            $em->remove($organization);
            $em->flush();

            $this->addFlash("success", "L'entité sélétionné a bien été supprimé");
        }

        return $this->redirectToRoute('organization.index');
    }
}
