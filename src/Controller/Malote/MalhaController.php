<?php

namespace App\Controller\Malote;

use App\Entity\Malote\Malha;
use App\Form\Malote\MalhaType;
use App\Repository\Malote\MalhaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/malote/malha")
 */
class MalhaController extends Controller
{
    /**
     * @Route("/", name="malote_malha_index", methods="GET")
     */
    public function index(MalhaRepository $malhaRepository): Response
    {
        return $this->render('malote/malha/index.html.twig', ['malhas' => $malhaRepository->findAll()]);
    }

    /**
     * @Route("/novo", name="malote_malha_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $malha = new Malha();
        $form = $this->createForm(MalhaType::class, $malha);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('malote');
            $em->persist($malha);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('malote_malha_index');
        }

        return $this->render('malote/malha/new.html.twig', [
            'malha' => $malha,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_malha_show", methods="GET")
     */
    public function show(Malha $malha): Response
    {
        return $this->render('malote/malha/show.html.twig', ['malha' => $malha]);
    }

    /**
     * @Route("/{id}/editar", name="malote_malha_edit", methods="GET|POST")
     */
    public function edit(Request $request, Malha $malha): Response
    {
        $form = $this->createForm(MalhaType::class, $malha);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('malote')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('malote_malha_index');
        }

        return $this->render('malote/malha/edit.html.twig', [
            'malha' => $malha,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_malha_delete", methods="DELETE")
     */
    public function delete(Request $request, Malha $malha): Response
    {
        if ($this->isCsrfTokenValid('delete'.$malha->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('malote');
            $em->remove($malha);
            $em->flush();
        }

        return $this->redirectToRoute('malote_malha_index');
    }

    /**
     * @Route("/{id}", name="malote_malha_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Malha $malha): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('malote.malha.change-status.title', ['%name%' => $malha->getNome()]);
        if ($this->isCsrfTokenValid('put'.$malha->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('malote');
            $malha->setAtivo(!$malha->getAtivo());
            $em->persist($malha);
            $em->flush();
            $message = $this->get('translator')->trans('malote.malha.change-status.success', ['%name%'=> $malha->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }
}
