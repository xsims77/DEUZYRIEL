<?php

namespace App\Template;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TemplateManager extends AbstractController
{
   
   public function display(Request $request, string $view, array $parameters = [])
   {
    $activeRelation = $request->getSession()->get('active_relation');
    $relations = $request->getSession()->get('relations');
    if ($activeRelation !== null) {
        $parameters['active_role'] = array_values($activeRelation)[0];
        $parameters['active_organization'] = array_key_first($activeRelation);
        $parameters['nb_relations'] = count($relations);
    }

    return $this->render($view, $parameters);
   }

}
