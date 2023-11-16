<?php

namespace App\Controller\Don;


use App\Entity\Donations;
use App\Form\DonFormType;
use App\Entity\MoralCustomers;
use App\Entity\PhysicalCustomers;
use App\Repository\DonationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DonController extends AbstractController
{
    #[Route('/don', name: 'don.index', methods:['GET'])]
    public function index(DonationsRepository $donationsRepository) : Response
    {
        $dons = $donationsRepository->findAll();

        return $this->render('pages/don/index.html.twig', [
            'dons'  => $dons
        ]);
    }

    #[Route('/don/{id}/physicalCreate', name: 'don.physicalCreate', methods:['GET', 'POST'])]
    public function donPhysicalCreate(PhysicalCustomers $physical, Request $request, EntityManagerInterface $em) : Response
    {

        $don = new Donations;

        $form = $this->createForm(DonFormType::class, $don);
        $form->handleRequest($request);
        $projectId = $form->get('project')->getData();

        if ($form->isSubmitted() && $form->isValid()) 
        {

            $don->setProject($projectId);
            $don->setPhysicalCustomer($physical);
            $em->persist($don);
            $em->flush();

            $this->addFlash("success","Le don a bien été ajouter" );
            return $this->redirectToRoute('don.index');
        }

        return $this->render('pages/don/physicalCreateDon.html.twig', [
            'form'      => $form->createView(),
            'physical'  => $physical
        ]);
    }

    #[Route('/don/{id}/moralCreate', name: 'don.moralCreate', methods:['GET', 'POST'])]
    public function donMoralCreate( MoralCustomers $moral, Request $request, EntityManagerInterface $em) : Response
    {

        $don = new Donations;

        $form = $this->createForm(DonFormType::class, $don);
        $form->handleRequest($request);
        $projectId = $form->get('project')->getData();

        if ($form->isSubmitted() && $form->isValid())
        {
            $don->setProject($projectId);
            $don->setMoralCustomer($moral);
            $em->persist($don);
            $em->flush();

            $this->addFlash("success","Le don a bien été ajouter" );
            return $this->redirectToRoute('don.index');

        }

        return $this->render('pages/don/moralCreateDon.html.twig', [
            'form'      => $form->createView(),
            'moral'     => $moral
        ]);
    }

    #[Route('/don/{id}/edit', name: 'don.edit', methods:['GET', 'PUT'])]
    public function donEdit(Donations $don, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(DonFormType::class, $don, [
            'method'    => 'PUT'
        ]);
        $form->handleRequest($request);
        $projectId = $form->get('project')->getData();

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $don->setProject($projectId);
            $em->persist($don);
            $em->flush();

            $this->addFlash("success","Le don a bien été modifier" );
            return $this->redirectToRoute('don.index');
        }

        return $this->render('pages/don/edit.html.twig', [
            'form'  => $form
        ]);
    }

    #[Route('don/{id}/delete', name: 'don.delete', methods:['DELETE'])]
    public function donDelete(Donations $don, Request $request, EntityManagerInterface $em) : Response
    {
        if ( $this->isCsrfTokenValid("don_delete_".$don->getId(), $request->request->get('csrf_token')) ) 
        {
            $em->remove($don);
            $em->flush();

            $this->addFlash("success", "Le don a été supprimé avec succès");
            
        }
        return $this->redirectToRoute('don.index');
    }
}
