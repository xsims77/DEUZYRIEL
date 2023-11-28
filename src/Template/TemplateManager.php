<?php

namespace App\Template;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateManager extends AbstractController
{
   public function display(Request $request, string $view, array $parameters = []): Response
   {
    $activeRelation = $request->getSession()->get('active_relation');
    $relations = $request->getSession()->get('relations');
    if ($activeRelation !== null) {
		  $parameters['is_agent'] = self::isRoleAgent($activeRelation['roleName']);
		  $parameters['is_delegate'] = self::isRoleDelegate($activeRelation['roleName']);
		  $parameters['is_admin'] = self::isRoleAdmin($activeRelation['roleName']);
      $parameters['active_role'] = $activeRelation['roleName'];
      $parameters['active_organization'] = $activeRelation['organizationId'];
      $parameters['nb_relations'] = count($relations);
    }

    return $this->render($view, $parameters);
   }
   
   //Cette fonction permet de vérifié si la session est toujours active
   protected function checkSession(Request $request): ?RedirectResponse
   {
	   if (!empty($request->getSession()->get('active_relation'))) {
		   return null;
	   }
	   
	   $this->addFlash("warning", "Un problème est survenue sur votre connexion, veuillez vous reconnecter.");
	   
	   return $this->redirectToRoute('app.logout');
   }

   public static function isRoleAdmin(string $value): bool
   {
	  return $value === 'ROLE_ADMIN';
   }
	
	public static function isRoleDelegate(string $value): bool
	{
		return $value === 'ROLE_DELEGATE';
	}
	
	public static function isRoleAgent(string $value): bool
	{
		return $value === 'ROLE_AGENT';
	}
}
