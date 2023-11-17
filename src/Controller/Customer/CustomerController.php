<?php

namespace App\Controller\Customer;

use App\Form\MoralFormType;
use App\Entity\MoralCustomers;
use App\Form\PhysicalFormType;
use App\Entity\PhysicalCustomers;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MoralCustomersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PhysicalCustomersRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CustomerController extends AbstractController
{
    #[Route('/customer', name: 'customer.index', methods:['GET'])]
    public function index(MoralCustomersRepository $moralCustomersRepository, PhysicalCustomersRepository $physicalCustomersRepository) : Response
    {
        $morals = $moralCustomersRepository->findAll();

        $physicals = $physicalCustomersRepository->findAll();

        return $this->render('pages/customer/index.html.twig', [
            "morals"    => $morals,
            "physicals" => $physicals
            
        ]);
    }

    #[Route('/customer/create', name: 'customer.create', methods:['GET','POST'])]
    public function donorCreate(Request $request, EntityManagerInterface $em) : Response
    {
        $physical = new PhysicalCustomers();
        $form = $this->createForm(PhysicalFormType::class, $physical);
        $form->handleRequest($request);
        $organizationId = $form->get('organization')->getData();
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $physical->setOrganization($organizationId);
            $em->persist($physical);
            $em->flush();
            
            $this->addFlash("success", "Le nouveau donateur a bien été pris en compte.");
            return $this->redirectToRoute('customer.index');
        }

        $moral = new MoralCustomers();
        $form1 = $this->createForm(MoralFormType::class, $moral);
        $form1->handleRequest($request);
        $organizationId = $form1->get('organization')->getData();
        
        if($form1->isSubmitted() && $form1->isValid())
        {
            $moral->setOrganization($organizationId);
            $em->persist($moral);
            $em->flush();

            $this->addFlash("success", "Le nouveau donateur a bien été pris en compte.");
            return $this->redirectToRoute('customer.index');
        }
        return $this->render('pages/customer/create.html.twig',[
            'physicalForm'  => $form->createView(),
            'moralForm'     => $form1->createView(),
        ]);
    }

    #[Route('/customer/{id}/physicalEdit', name: 'customer.physicalEdit', methods:['GET','PUT'])]
    public function physicalEdit(PhysicalCustomers $physical, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(PhysicalFormType::class, $physical, [
            'method'    => 'PUT'
        ]);
        $form->handleRequest($request);
        $organizationId = $form->get('organization')->getData();

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $physical->setOrganization($organizationId);
            $em->persist($physical);
            $em->flush();

            $this->addFlash("success", "Les modifications de ce donateur a bien été pris en compte.");
            return $this->redirectToRoute('customer.index');
        }
        return $this->render('pages/customer/physicalEdit.html.twig', [
            'physicalForm'  => $form->createView(),
            'physical'      => $physical
        ]);
    }

    #[Route('/customer/{id}/physicalDelete', name: 'customer.physicalDelete', methods:['DELETE'])]
    public function physicalDelete(Request $request, PhysicalCustomers $physical, EntityManagerInterface $em) : Response
    {
        if ( $this->isCsrfTokenValid("physical_delete_".$physical->getId(), $request->request->get("csrf_token") ) ) 
        {
            $em->remove($physical);
            $em->flush();

            $this->addFlash("success", "La suppression du donateur a bien été prise en compte.");
        }
        return $this->redirectToRoute('customer.index');
    }

    #[Route('/customer/{id}/moralEdit', name: 'customer.moralEdit', methods:['GET','POST'])]
    public function moralEdit(MoralCustomers $moral, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(MoralFormType::class, $moral);

        $form->handleRequest($request);

        $organizationId = $form->get('organization')->getData();

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $moral->setOrganization($organizationId);

            $em->persist($moral);
            $em->flush();

            $this->addFlash("success","Les modifications de ce donateur a bien été pris en compte.");
            return $this->redirectToRoute('customer.index');
        }
        return $this->render('pages/customer/moralEdit.html.twig', [
            'moralForm' => $form->createView(),
            'moral'     => $moral
        ]);
    }

    #[Route('/customer/{id}/moralDelete', name: 'customer.moralDelete', methods:['DELETE'])]
    public function moralDelete(MoralCustomers $moral, Request $request, EntityManagerInterface $em) : Response
    {
        if ( $this->isCsrfTokenValid("moral_delete_".$moral->getId(), $request->request->get("csrf_token")) ) 
        {
            $em->remove($moral);
            $em->flush();

            $this->addFlash("success", "La suppression du donateur a bien été prise en compte.");
        }
        return $this->redirectToRoute('customer.index');
    }


    
}
