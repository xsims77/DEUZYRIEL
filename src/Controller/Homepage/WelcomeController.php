<?php

namespace App\Controller\Homepage;

use App\Template\TemplateManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends TemplateManager
{
    #[Route('/', name: 'welcome.index')]
    public function index(Request $request): Response
    {
        return $this->display($request, 'pages/welcome/index.html.twig');
    }
}
