<?php

namespace App\Controller\Main;

use App\Entity\Main\Unidade;
use App\Form\Main\UnidadeType;
use App\Repository\Main\UnidadeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/geral/unidade")
 */
class UnidadeController extends AbstractController
{
    /**
     * @Route("/", name="main_unidade_index", methods="GET")
     */
    public function index(UnidadeRepository $unidadeRepository): Response
    {
        return $this->render('main/unidade/index.html.twig', ['unidades' => $unidadeRepository->findAll()]);
    }

    /**
     * @Route("/nova", name="main_unidade_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $unidade = new Unidade();
        $form = $this->createForm(UnidadeType::class, $unidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($unidade);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('main_unidade_index');
        }

        return $this->render('main/unidade/new.html.twig', [
            'unidade' => $unidade,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="main_unidade_show", methods="GET")
     */
    public function show(Unidade $unidade): Response
    {
        return $this->render('main/unidade/show.html.twig', ['unidade' => $unidade]);
    }

    /**
     * @Route("/{id}/editar", name="main_unidade_edit", methods="GET|POST")
     */
    public function edit(Request $request, Unidade $unidade): Response
    {
        $form = $this->createForm(UnidadeType::class, $unidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('main_unidade_edit', ['id' => $unidade->getId()]);
        }

        return $this->render('main/unidade/edit.html.twig', [
            'unidade' => $unidade,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="main_unidade_delete", methods="DELETE")
     */
    public function delete(Request $request, Unidade $unidade): Response
    {
        if ($this->isCsrfTokenValid('delete'.$unidade->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($unidade);
            $em->flush();
        }

        return $this->redirectToRoute('main_unidade_index');
    }

    /**
     * @Route("/{id}", name="main_unidade_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Unidade $unidade): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('main.unidade.change-status.title', ['%name%' => $unidade->getNome()]);
        if ($this->isCsrfTokenValid('put'.$unidade->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $unidade->setAtivo(!$unidade->getAtivo());
            $em->persist($unidade);
            $em->flush();
            $message = $this->get('translator')->trans('main.unidade.change-status.success', ['%name%'=> $unidade->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }
}
