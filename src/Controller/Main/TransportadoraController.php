<?php

namespace App\Controller\Main;

use App\Entity\Main\Transportadora;
use App\Form\Main\TransportadoraType;
use App\Repository\Main\TransportadoraRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/geral/transportadora")
 */
class TransportadoraController extends AbstractController
{
    /**
     * @Route("/", name="main_transportadora_index", methods="GET")
     */
    public function index(TransportadoraRepository $transportadoraRepository): Response
    {
        return $this->render('main/transportadora/index.html.twig', ['transportadoras' => $transportadoraRepository->findAll()]);
    }

    /**
     * @Route("/novo", name="main_transportadora_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $transportadora = new Transportadora();
        $form = $this->createForm(TransportadoraType::class, $transportadora);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($transportadora);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('main_transportadora_index');
        }

        return $this->render('main/transportadora/new.html.twig', [
            'transportadora' => $transportadora,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="main_transportadora_show", methods="GET")
     */
    public function show(Transportadora $transportadora): Response
    {
        return $this->render('main/transportadora/show.html.twig', ['transportadora' => $transportadora]);
    }

    /**
     * @Route("/{id}/editar", name="main_transportadora_edit", methods="GET|POST")
     */
    public function edit(Request $request, Transportadora $transportadora): Response
    {
        $form = $this->createForm(TransportadoraType::class, $transportadora);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('main_transportadora_edit', ['id' => $transportadora->getId()]);
        }

        return $this->render('main/transportadora/edit.html.twig', [
            'transportadora' => $transportadora,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="main_transportadora_delete", methods="DELETE")
     */
    public function delete(Request $request, Transportadora $transportadora): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transportadora->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transportadora);
            $em->flush();
        }

        return $this->redirectToRoute('main_transportadora_index');
    }
}
