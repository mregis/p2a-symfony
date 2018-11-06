<?php

namespace App\Controller\Malote;

use App\Entity\Malote\Malote;
use App\Form\Malote\MaloteType;
use App\Repository\Malote\MaloteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/malote/malote")
 */
class MaloteController extends Controller
{
    /**
     * @Route("/", name="malote_malote_index", methods="GET")
     */
    public function index(MaloteRepository $maloteRepository): Response
    {
        return $this->render('malote_malote/index.html.twig', ['malotes' => $maloteRepository->findAll()]);
    }

    /**
     * @Route("/new", name="malote_malote_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $malote = new Malote();
        $form = $this->createForm(MaloteType::class, $malote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($malote);
            $em->flush();

            return $this->redirectToRoute('malote_malote_index');
        }

        return $this->render('malote_malote/new.html.twig', [
            'malote' => $malote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_malote_show", methods="GET")
     */
    public function show(Malote $malote): Response
    {
        return $this->render('malote_malote/show.html.twig', ['malote' => $malote]);
    }

    /**
     * @Route("/{id}/edit", name="malote_malote_edit", methods="GET|POST")
     */
    public function edit(Request $request, Malote $malote): Response
    {
        $form = $this->createForm(MaloteType::class, $malote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('malote_malote_edit', ['id' => $malote->getId()]);
        }

        return $this->render('malote_malote/edit.html.twig', [
            'malote' => $malote,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_malote_delete", methods="DELETE")
     */
    public function delete(Request $request, Malote $malote): Response
    {
        if ($this->isCsrfTokenValid('delete'.$malote->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($malote);
            $em->flush();
        }

        return $this->redirectToRoute('malote_malote_index');
    }
}
