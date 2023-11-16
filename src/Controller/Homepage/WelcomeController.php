<?php

namespace App\Controller\Homepage;


use App\Repository\RelationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WelcomeController extends AbstractController
{
    #[Route('/', name: 'welcome.index')]
    public function index(RelationRepository $relationRepository): Response
    {
        $relations = $relationRepository->findAll();
        return $this->render('pages/welcome/index.html.twig', [
            'relations' => $relations
        ]);
    }
}
