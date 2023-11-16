<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Form\ProjectFormType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
    #[Route('/project', name: 'project.index', methods:['GET'])]
    public function project(ProjectRepository $projectRepository) : Response
    {

        $projects = $projectRepository->findBy([], ['createdAt'=> 'DESC']);

        return $this->render('pages/project/index.html.twig', compact('projects'));
    }

    #[Route('/project/create', name: 'project.create', methods:['GET', 'POST'])]
    public function projectCreate(Request $request, EntityManagerInterface $em, OrganizationRepository $organizationRepository) : Response
    {

        if( count($organizationRepository->findAll()) == 0)
        {
            $this->addFlash("warning", "Vous devez créer au moins une organisation avant de créer un projet.");
            return $this->redirectToRoute('organization.index');
        }


        $project = new Project();

        $form = $this->createForm(ProjectFormType::class, $project);
        
        $form->handleRequest($request);

        $organizationId = $form->get('organizationName')->getData();
        
        if ($form->isSubmitted() && $form->isValid())
        {
            
            $project->setOrganization($organizationId);
            $em->persist($project);
            $em->flush();
            
            $this->addFlash("success", "Le projet a bien été validé");
            return $this->redirectToRoute('project.index');
        }

        return $this->render('pages/project/create.html.twig', [
            'projectForm'   => $form->createView()
        ]);
    }

    #[Route('/project/{id}/edit', name: 'project.edit', methods:['GET', 'PUT'])]
    public function projectEdit(Project $project, Request $request, EntityManagerInterface $em) : Response
    {

        $form = $this->createForm(ProjectFormType::class, $project, [
            "method"    => 'PUT'
        ]);

        $form->handleRequest($request);

        $organizationId = $form->get('organizationName')->getData();

        if ($form->isSubmitted() && $form->isValid())
        {


            $project->setOrganization($organizationId);

            $em->persist($project);
            $em->flush();


            $this->addFlash("success", "Les modifications du projets ont été prises en compte.");
            return $this->redirectToRoute('project.index');
        }

        return $this->render('pages/project/edit.html.twig', [
            "projectForm"   => $form->createView(),
            "project"       => $project
        ]);
    }

    #[Route('/project/{id}/show', name: 'project.show', methods:['GET'])]
    public function projectShow(Project $project, OrganizationRepository $organizationRepository) : Response
    {
        if ( count($organizationRepository->findAll()) == 0)
        {
            $this->addFlash("warning", "Vous devez créer au moin une entitée ou le nom de la société avant créer un projet.");

            return $this->redirectToRoute('admin.organization');
        }
        return $this->render("pages/project/show.html.twig", compact("project"));
    }

    #[Route('/project/{id}/delete', name: 'project.delete', methods:['DELETE'])]
    public function projectDelete(Project $project, Request $request, EntityManagerInterface $em ) : Response
    {
            
            if($this->isCsrfTokenValid("project_delete_".$project->getId(), $request->request->get("csrf_token")) )
            {
                
                $em->remove($project);
                $em->flush();

                $this->addFlash("success", "La suppression du projet a bien été prise en compte.");
            }
            return $this->redirectToRoute('project.index');
    }

}
