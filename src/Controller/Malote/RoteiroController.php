<?php

namespace App\Controller\Malote;

use App\Entity\Malote\Roteiro;
use App\Form\Malote\RoteiroType;
use App\Repository\Malote\RoteiroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/malote/roteiro")
 */
class RoteiroController extends Controller
{
    /**
     * @Route("/", name="malote_roteiro_index", methods="GET")
     */
    public function index(RoteiroRepository $roteiroRepository): Response
    {
        return $this->render('malote/roteiro/index.html.twig', ['roteiros' => $roteiroRepository->findAll()]);
    }

    /**
     * @Route("/novo", name="malote_roteiro_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $roteiro = new Roteiro();
        $form = $this->createForm(RoteiroType::class, $roteiro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('malote');
            $em->persist($roteiro);
            $em->flush();

            return $this->redirectToRoute('malote_roteiro_index');
        }

        return $this->render('malote/roteiro/new.html.twig', [
            'roteiro' => $roteiro,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_roteiro_show", methods="GET")
     */
    public function show(Roteiro $roteiro): Response
    {
        return $this->render('malote/roteiro/show.html.twig', ['roteiro' => $roteiro]);
    }

    /**
     * @Route("/{id}/editar", name="malote_roteiro_edit", methods="GET|POST")
     */
    public function edit(Request $request, Roteiro $roteiro): Response
    {
        $form = $this->createForm(RoteiroType::class, $roteiro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('malote')->flush();

            return $this->redirectToRoute('malote_roteiro_edit', ['id' => $roteiro->getId()]);
        }

        return $this->render('malote/roteiro/edit.html.twig', [
            'roteiro' => $roteiro,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_roteiro_delete", methods="DELETE")
     */
    public function delete(Request $request, Roteiro $roteiro): Response
    {
        if ($this->isCsrfTokenValid('delete'.$roteiro->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('malote');
            $em->remove($roteiro);
            $em->flush();
        }

        return $this->redirectToRoute('malote_roteiro_index');
    }

/**
 * @Route("/{id}", name="malote_roteiro_changestatus", methods="PUT")
 */
public function changeStatus(Request $request, Roteiro $roteiro): Response
{
    $message = 'basic-error';
    $statusMode = 'danger';
    $title = $this->get('translator')->trans('malote.roteiro.change-status.title', ['%name%' => $roteiro->getNome()]);
    if ($this->isCsrfTokenValid('put'.$roteiro->getId(), $request->request->get('_token'))) {
        $em = $this->getDoctrine()->getManager('malote');
        $roteiro->setAtivo(!$roteiro->getAtivo());
        $em->persist($roteiro);
        $em->flush();
        $message = $this->get('translator')->trans('malote.malha.change-status.success', ['%name%'=> $roteiro->getNome()]);
        $statusMode = 'success';
    }

    return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
}
}
