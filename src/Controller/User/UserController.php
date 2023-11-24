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
        $activeRelation = $request->getSession()->get('active_relation');
        $role = $rolesRepository->findOneBy(['id' => array_values($activeRelation)[0]]);

        if ($role->getRoleName() == "ROLE_AGENT")
        {
            return $this->redirectToRoute('welcome.index');
        }

        if ($role->getRoleName() == "ROLE_ADMIN") 
        {
            $users = $usersRepository->findAll();
        }
        else 
        {
            $users = $usersRepository->findAllByOrganization(array_key_first($activeRelation));
        }

        return $this->display($request, 'pages/users/index.html.twig', [
            'users' => $users,
            'role'  => $role
        ]);
    }

    #[Route('/users/create', name: 'users.create', methods:['GET','POST'])]
    public function create(Request $request,  UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, OrganizationRepository $organizationRepository, RolesRepository $rolesRepository): Response
    {
        
        if( count($organizationRepository->findAllNonAdmin()) == 0)
        {
            $this->addFlash("warning", "Vous devez créer au moins une organisation pour accéder au formulaire d'un nouvel utilisateur.");   
            return $this->redirectToRoute('organization.index');
        }
        $role = $rolesRepository->findAll();

        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = array_values($activeRelation)[0];
        $activeOrganizationId = array_key_first($activeRelation);

        $user = new Users();
        $form = $this->createForm(UserFormType::class, $user, ['role_id' => $activeRoleId]);
        
        $form->handleRequest($request);
        if ($activeRoleId == 1) {
            $roleEntity = $form->get('roleName')->getData();
            $organizationEntity = $form->get('organizationName')->getData();
        } else {
            $organizationEntity = $organizationRepository->findOneBy(['id' => $activeOrganizationId]);
        }

        if($form->isSubmitted() && $form->isValid())
        {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $em->persist($user);
            $em->flush();
            
            $relation = new Relation();
            $relation->setUser($user);
            if ($activeRoleId == 1) {
                $relation->setRole($roleEntity);
            }   else {
                $relation->setRole($role[2]);
            }
            $relation->setOrganization($organizationEntity);
            $em->persist($relation);
            $em->flush();
            
            $this->addFlash("success", "le nouvel utilisateur a été ajoutée avec succès.");
            return $this->redirectToRoute("users.index");

        }

       return $this->display($request, 'pages/users/create.html.twig', [
            'form'  => $form->createView()
        ]);
     
    }

    #[Route('/users/{id}/edit', name: 'users.edit', methods:['GET','PUT'])]
    public function edit(Users $user, Request $request,  UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
    {

        $activeRelation = $request->getSession()->get('active_relation');
        $activeRoleId = array_values($activeRelation)[0];
        
        $form = $this->createForm(UserFormType::class, $user, [
            'role_id'   => $activeRoleId,
            'method'    => 'PUT'
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {   
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                    )
                );
            
            $em->persist($user);
            $em->flush();
            
            $this->addFlash("success", "L'utilisateur a bien été modifié");
            return $this->redirectToRoute("users.index");
        }

        return $this->display($request, 'pages/users/edit.html.twig',[
            "userEditForm"  => $form->createView(),
            "user"          => $user
        ]);
    }
    
    #[Route('/users/{id}/delete', name: 'users.delete', methods:['DELETE'])]
    public function Delete(Users $user, Request $request, EntityManagerInterface $em) : Response
    {
        // if ($this->isCsrfTokenValid("users_delete_". $user->getId(), $request->request->get('csrf_token')))
        // {
        //     $em->remove($user);
        //     $em->flush();

        //     $this->addFlash("success", "L'utilisateur sélétionné a bien été supprimé");
        // }
        // return $this->redirectToRoute('users.index');

        // ******* Early return // retour tôt code par mon formateur de stage a connaître

        if (!$this->isCsrfTokenValid("users_delete_". $user->getId(), $request->request->get('csrf_token'))) 
        {
            $this->addFlash("warning", "Un problème est survennue lors de la suppression.");
            return $this->redirectToRoute('users.index');
        }

        $em->remove($user);
        $em->flush();
        $this->addFlash("success", "L'utilisateur sélétionné a bien été supprimé");

        return $this->redirectToRoute('users.index');

    }

    // Partie relation du users

    #[Route('/users/relation/{id}', name: 'users.relation.create', methods:['GET', 'POST'])]
    public function relationCreate(Request $request, EntityManagerInterface $em, UsersRepository $usersRepository, RolesRepository $rolesRepository, OrganizationRepository $organizationRepository) : Response
    {
        $relation = new Relation();
        $form = $this->createForm(RelationFormType::class, $relation);
        $form->handleRequest($request);
        if ($form->isSubmitted()) 
        {       
            if (!$form->isValid()) 
            {
                $this->addFlash('warning', 'un problème est survenue lors de la validation, veuillez vérifier que tout les champs sont bien sélectionnés.');
                return $this->display($request, 'pages/users/relation/create.html.twig', [
                    'form'  => $form
                ]);
            }
            $relation->setUser($usersRepository->findOneBy(['id' => $request->get('id')]));
            $relation->setRole($rolesRepository->findOneBy(['id' => $form->get('role')->getData()]));
            $relation->setOrganization($organizationRepository->findOneBy(['id' => $form->get('organization')->getData()]));
            $em->persist($relation);
            $em->flush();

            $this->addFlash('success', 'La relation de l\'utilisateur a bien été pris en compte.');
            return $this->redirectToRoute('users.index');
        }


        return $this->display($request, 'pages/users/relation/create.html.twig', [
            'form'  => $form,

        ]);
    }
}
