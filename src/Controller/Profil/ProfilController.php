<?php

namespace App\Controller\Profil;

use App\Form\ProfilFormType;
use App\Template\TemplateManager;
use App\Form\EditProfilPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends TemplateManager
{
    #[Route('/profil', name: 'profil.index', methods:["GET"])]
    public function index(Request $request) :Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        return $this->display($request, "pages/profil/profil.html.twig");
    }
	
    #[Route('/profil/edit', name: 'profil.edit', methods:['GET', 'PUT'])]
    public function profilEdit(Request $request, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $userEntity = $this->getUser();
        $form = $this->createForm(ProfilFormType::class, $userEntity, [
            "method"    => "PUT"
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($userEntity);
            $em->flush();
            $this->addFlash("success", "Vos coordonnés ont bien été modifier");
            
            return $this->redirectToRoute('profil.index');
        }
		
        return $this->display($request, 'pages/profil/edit.html.twig', [
            "profilForm"    => $form->createView()
        ]);
    }

    #[Route('/profil/edit_password', name: 'profil.edit_password', methods:['GET','PUT'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em) : Response
    {
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
        $userEntity = $this->getUser();
        $form = $this->createForm(EditProfilPasswordFormType::class, null, [
            "method"    => "PUT"
        ]);
        $form->handleRequest($request);
   
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();
            $password = $data['edit_profil_password_form']['password']['first'];
            $passwordHashed = $hasher->hashPassword($userEntity, $password);
            $userEntity->setPassword($passwordHashed);

            $em->persist($userEntity);
            $em->flush();
            $this->addFlash("success", "Votre nouveau mot de passe a bien été pris en compte");

            return $this->redirectToRoute('profil.index');
        }

        return $this->display($request, "pages/profil/edit_password.html.twig", [
            'form'  => $form->createView()
        ]);
    }
}
