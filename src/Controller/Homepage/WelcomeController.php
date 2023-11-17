<?php

namespace App\Controller\Homepage;


use App\Repository\RelationRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WelcomeController extends AbstractController
{
    #[Route('/', name: 'welcome.index')]
    public function index(UsersRepository $user,RelationRepository $relationRepository): Response
    {
        $relations = $relationRepository->findAll();
        // dd($relations);

        return $this->render('pages/welcome/index.html.twig', [
            'relations' => $relations
        ]);
    }
}
