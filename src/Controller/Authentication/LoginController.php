<?php

namespace App\Controller\Authentication;

use App\Template\TemplateManager;
use App\Repository\RelationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends TemplateManager
{
    #[Route(path: '/login', name: 'authentication.login')]
    public function login(AuthenticationUtils $authenticationUtils, RelationRepository $relationRepository, Request $request): Response
    {
        $user = $this->getUser();
        if ($user) {
            $user->updateRelation($relationRepository);
            $relations = $user->getRelations();

            if (empty($relations)) {
                // mettre un message d'erreur
                $this->addFlash('warning',"un problème est survenu lors de votre connexion");
                // faire une erreur et renvoyer sur la page de déconnexion
                return $this->redirectToRoute('app.logout');
            }

            $newRelations = [];
            foreach ($relations as $relation) {
                // $relation est un objet de type RelationEntity
                // on va ajouter (ou récupérer si ça existe déjà)
                // l'id organization qui est existant dans l'objet $relation
                // pareil pour le role
                // ça nous permettra de savoir directement à partir de $newRelations
                // quel est le role pour chaque organization
                // pour ce user
                $newRelations[$relation->getOrganization()->getId()] = $relation->getRole()->getId();
            }
            // on vire les anciennes données de session pour la value relations et on remet les nouvelles
            $request->getSession()->remove('relations');
            $request->getSession()->set('relations', $newRelations);

            // on fonctionne avec une relation active, ce qui permet de savoir directement quel compte/profile/role est actif en cours pour quelle organisation
            // là on supprime pour pouvoir remettre une nouvelle donnée
            $request->getSession()->remove('active_relation');

            // si il n'y a qu'une seule relation, alors on la met par défaut et on redirige le mec vers le site
            if (count($relations) == 1) {    
                $request->getSession()->set('active_relation', $newRelations);

                return $this->redirectToRoute('welcome.index');
            }

            // si le mec a plusieurs relations, alors on prend la première qu'on trouve et on la définit en tant que relation active (si on ne choisit rien et que le mec ne choisit rien aussi, ça peut foutre en l'air le site)
            // il aura la possibilité de choisir si il veut changer d eprofile ou non après sur la page change_profile
            $idActive = array_key_first($newRelations);
            $activeRelation = [$idActive => $newRelations[$idActive]];
            $request->getSession()->set('active_relation', $activeRelation);

            return $this->redirectToRoute('welcome.index'); // change to "change_profile"
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->display($request, 'pages/authentication/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app.logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
