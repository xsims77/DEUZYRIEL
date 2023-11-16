<?php

namespace App\Controller\User;

use App\Entity\Users;
use App\Entity\Relation;
use App\Form\UserFormType;
use App\Repository\RolesRepository;
use App\Repository\UsersRepository;
use App\Repository\RelationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/users', name: 'users.index', methods:['GET'])]
    public function index(UsersRepository $usersRepository): Response
    {
        $users = $usersRepository->findAll();

        return $this->render('pages/users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/create', name: 'users.create', methods:['GET','POST'])]
    public function create(Request $request,  UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, OrganizationRepository $organizationRepository): Response
    {
        
        if( count($organizationRepository->findAll()) == 0)
        {
            $this->addFlash("warning", "Vous devez créer au moins une organisation pour accéder au formulaire d'un nouvel utilisateur.");   
            return $this->redirectToRoute('organization.index');
        }
    
        $user = new Users();
        
        $form = $this->createForm(UserFormType::class, $user);
        
        $form->handleRequest($request);
        $roleId = $form->get('roleName')->getData();
        $organizationId = $form->get('organizationName')->getData();
        
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
            $relation->setRole($roleId);
            $relation->setOrganization($organizationId);
            
            $em->persist($relation);
            $em->flush();
            
            $this->addFlash("success", "le nouvel utilisateur a été ajoutée avec succès.");
            return $this->redirectToRoute("users.index");

        }

       return $this->render('pages/users/create.html.twig', [
            'form'  => $form->createView()
        ]);
     
    }

    #[Route('/users/{id}/edit', name: 'users.edit', methods:['GET','PUT'])]
    public function edit(Users $user, Request $request,  UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, RelationRepository $relationRepository, RolesRepository $rolesRepository, OrganizationRepository $organizationRepository): Response
    {
        $form = $this->createForm(UserFormType::class, $user, [
            "method" => "PUT"
        ]);
        
        $form->handleRequest($request);
        $roleId = $form->get('roleName')->getData();
        $organizationId = $form->get('organizationName')->getData();

        if ($form->isSubmitted() && $form->isValid())
        {   
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                    )
                );

            $role = $rolesRepository->findOneBy(['id'=>$roleId]);
            $entity = $organizationRepository->findOneBy(['id'=>$organizationId]);
            $relation = $relationRepository->findOneBy(['user'=>$user]);        
            
            $relation->setRole($role);
            $relation->setOrganization($entity);
            
            $em->persist($user);
            $em->flush();
            
            $this->addFlash("success", "L'utilisateur a bien été modifié");
            return $this->redirectToRoute("users.index");
        }

        return $this->render('pages/users/edit.html.twig',[
            "userEditForm"  => $form->createView(),
            "user"          => $user
        ]);
    }
    
    #[Route('/users/{id}/delete', name: 'users.delete', methods:['DELETE'])]
    public function Delete(Users $user, Request $request, EntityManagerInterface $em) : Response
    {
        if ($this->isCsrfTokenValid("users_delete_". $user->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($user);
            $em->flush();

            $this->addFlash("success", "L'utilisateur sélétionné a bien été supprimé");
        }
        return $this->redirectToRoute('users.index');
    }
}
