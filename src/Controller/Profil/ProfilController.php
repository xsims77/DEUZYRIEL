<?php

namespace App\Controller\Profil;

use App\Form\ProfilFormType;
use App\Form\EditProfilPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil.index', methods:["GET"])]
    public function index() :Response
    {
        return $this->render("pages/profil/profil.html.twig");
    }

    
    #[Route('/profil/edit', name: 'profil.edit', methods:['GET', 'PUT'])]
    public function profilEdit(Request $request, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        
        $form = $this->createForm(ProfilFormType::class, $user, [
            "method"    => "PUT"
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {

            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Vos coordonnés ont bien été modifier");
            
            return $this->redirectToRoute('profil.index');
        }
        return $this->render('pages/profil/edit.html.twig', [
            "profilForm"    => $form->createView()
        ]);
    }

    #[Route('/profil/edit_password', name: 'profil.edit_password', methods:['GET','PUT'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfilPasswordFormType::class, null, [
            "method"    => "PUT"
        ]);

        $form->handleRequest($request);
   
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $data = $request->request->all();
            $password = $data['edit_profil_password_form']['password']['first'];
            $passwordHashed = $hasher->hashPassword($user, $password);
            $user->setPassword($passwordHashed);

            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "Votre nouveau mot de passe a bien été pris en compte");

            return $this->redirectToRoute('profil.index');
        }

        return $this->render("pages/profil/edit_password.html.twig", [
            'form'  => $form->createView()
        ]);
    }
}
