<?php

namespace App\Controller\Authentication;

use App\Template\TemplateManager;
use App\Repository\RelationRepository;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends TemplateManager
{
    #[Route(path: '/login', name: 'authentication.login')]
    public function login(AuthenticationUtils $authenticationUtils, RelationRepository $relationRepository, Request $request): Response
    {
        $userEntity = $this->getUser();
		// early return
		// si le user est vide, on le redirige direct sur la page d'authentification
        if (empty($userEntity)) {
			$error = $authenticationUtils->getLastAuthenticationError();
			$lastUsername = $authenticationUtils->getLastUsername();
			
			return $this->display($request, 'pages/authentication/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        }
		
		// on récupère/réactualise toutes les relations du user
		$userEntity->updateRelation($relationRepository);
		$relationEntity = $userEntity->getRelations();

		// early return
		if (empty($relationEntity)) {
			$this->addFlash('warning',"un problème est survenu lors de votre connexion");
			return $this->redirectToRoute('app.logout');
		}
		
		$newRelations = [];
		foreach ($relationEntity as $relation) {
			$newRelations[] = [
				'organizationId' => $relation->getOrganization()->getId(),
				'roleId'         => $relation->getRole()->getId(),
				'roleName'       => $relation->getRole()->getRoleName()
			];
		}

		// on supprime la session "relations" pour être sûr qu'on reparte de 0
		$request->getSession()->remove('relations');
		$request->getSession()->set('relations', $newRelations);
		
		// on supprime la session "active_relation" pour être sûr qu'on reparte de 0
		$request->getSession()->remove('active_relation');
		// on met tout de suite par défaut la 1ere relation en temps que "active_relation" pour qu'on ait pas de souci dans le fonctionnement du site
		$request->getSession()->set('active_relation', $newRelations[0]);
		
		// par défaut, la route de redirection est celle de sélection du profil
		$route = 'change_profil.index';
		// si il n'y a qu'une seule relation, alors on change la route vers la page d'accueil
		if (count($newRelations) == 1) {
			$route = 'welcome.index';
		}
		
		return $this->redirectToRoute($route);
    }

    #[Route(path: '/logout', name: 'app.logout')]
    public function logout(): void
    {
		// on évite d'appeler des classes en utilisant le chemin absolu, on préfèrera importer la classe
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
