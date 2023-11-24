<?php

namespace App\Controller\ChangeProfil;

use App\Template\TemplateManager;
use App\Repository\RolesRepository;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangeProfilController extends TemplateManager
{
    #[Route('/change/profil', name: 'change_profil.index', methods: ['GET'])]
    public function index(Request $request, RolesRepository $rolesRepository, OrganizationRepository $organizationRepository): Response
    {

        // récupérer toutes les relations
        // faire une liste des relations cliquables
        // mettre en value que l'id organization (comme ça c'est plus simple)
        // ajouter un bouton "changer" sauf pour celui qui est actuellement dans "active_relation"
        // quand on clique sur changer une relation
        // ça doit changer l'active relation pour celui qu'on vient de choisir
        // créer une nouvelle route qui permettra de changer la relation
          //---------------------

          $relations = $request->getSession()->get('relations');
          $activeRelation = $request->getSession()->get('active_relation');
          $affichages = [];
          // pour chaque ligne de relations, on récupère la clé dans organizationid et la valeur dans roleId
          foreach ($relations as $organizationId => $roleId) {
            $organization = $organizationRepository->findOneBy(['id' => $organizationId]);
            $role = $rolesRepository->findOneBy(['id' => $roleId]);

            $affichages[] = [
                'organizationId' => $organizationId,
                'organizationName' => $organization->getOrganizationName(),
                'roleName' => $role->getRoleName(),
                'isActive' => array_key_first($activeRelation) == $organizationId
            ];
            // isActive, on vérifie que l'id organization en cours est celui qui est enregistré dans l'active relation
          }
        // faire une boucle sur les relations pour récup id orga + id role
        // faire appel aux repo pour faire findOneBy
        // récupérer le nom de l'orga + nom du role
        // faire un nouveau tableau avec ces infos
        // vérifier si id orga dans la boucle = id orga relation active
        // si oui, ajouter param "is_active"
        // envoyer ce tableau dans twig
        // dans twig, if "is_active" == true, ne pas afficher le bouton "changer"
        return $this->display($request, 'pages/changeProfil/index.html.twig', [
            'affichages' => $affichages
        ]);
    }
    // --- 
    // dans la nouvelle route
    // on récupère l'id qui est envoyé dans l'url
    // on vérifie si l'id envoyé (qui est l'id organization) existe bien dans les relations qui sont enregistrées dans la session
    // si non, on l'envoie chier
    // si oui, on récupère le role associé et on enregistre
    // l'id orga + id role dans $relation
    // et $relation est la value qu'on enregistre en session pour active_relation
    // voir comment c'est fait dans LoginController
    // rediriger vers /changeProfil + flashmessage ok
    #[Route('/change/profil/{id}', name: 'change_profil.change', methods: ['GET'])]
    public function change(Request $request) : Response
    {

        $organizationId = $request->get('id');
        $relations = $request->getSession()->get('relations');
        if (!isset($relations[$organizationId])) 
        {
            // message erreur
            $this->addFlash("warning", "Un problème est survenue sur votre connexion, veuillez vous reconnecter.");
            return $this->redirectToRoute('app.logout');
        }

        $newRelation = [
            $organizationId => $relations[$organizationId]
        ];
        // pour rappel
        // $relations = tableau en session avec toutes les relations en base
        // $relations => clé ID ORGA => value ID ROLE
        $request->getSession()->remove('active_relation');
        $request->getSession()->set('active_relation', $newRelation);

        // faire un redirect vers changeProfil + flashmessage

        $this->addFlash('succes','Votre changement de profil a été effectué.');
        return $this->redirectToRoute('change_profil.index');
    }
}
