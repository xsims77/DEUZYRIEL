<?php

namespace App\Controller\ChangeProfil;

use App\Template\TemplateManager;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangeProfilController extends TemplateManager
{
	#[Route('/change/profil', name: 'change_profil.index', methods: ['GET'])]
	public function index(Request $request, OrganizationRepository $organizationRepository): Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$relations = $request->getSession()->get('relations');
		$activeRelation = $request->getSession()->get('active_relation');
		$data = [];
		foreach ($relations as $relation) {
			$organizationEntity = $organizationRepository->findOneBy(['id' => $relation['organizationId']]);
			$data[] = [
				'organizationId'   => $relation['organizationId'],
				'organizationName' => $organizationEntity->getOrganizationName(),
				'roleName'         => $activeRelation['roleName'],
				'isActive'         => $activeRelation['organizationId'] == $relation['organizationId']
			];
		}
		
		return $this->display($request, 'pages/changeProfil/index.html.twig', [
			'affichages' => $data
		]);
	}
	
	#[Route('/change/profil/{id}', name: 'change_profil.change', methods: ['GET'])]
	public function change(Request $request) : Response
	{
		// on vérifie si la session est toujours existante, sinon on déconnecte le user
		$this->checkSession($request);
		
		$organizationId = $request->get('id');
		$relations = $request->getSession()->get('relations');
		
		foreach ($relations as $relation) {
			// si ce n'est pas le bon id organization, on passe directement à la suite
			if ($relation['organizationId'] != $organizationId) {
				continue;
			}
			
			// si on trouve la relation qui a le bon id organization, on enregistre cette relation en tant que "active_relation" directement
			$request->getSession()->remove('active_relation');
			$request->getSession()->set('active_relation', $relation);
			$this->addFlash('success','Votre changement de profil a été effectué.');
			
			return $this->redirectToRoute('change_profil.index');
		}
		
		// si on a pas trouvé l'id organization, on affiche un message d'erreur et on redirige vers la page de déconnexion
		$this->addFlash("warning", "Un problème est survenue sur votre connexion, veuillez vous reconnecter.");
		
		return $this->redirectToRoute('app.logout');
		
	}
}
