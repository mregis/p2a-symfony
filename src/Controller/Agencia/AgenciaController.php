<?php

namespace App\Controller\Agencia;

use App\Entity\Agencia\Agencia;
use App\Form\Agencia\AgenciaType;
use App\Repository\Agencia\AgenciaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agencias")
 */
class AgenciaController extends Controller
{
    /**
     * @Route("/", name="agencias-home", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('agencias/index.html.twig');
    }

    /**
     * @Route("/agencia", name="list-agencias", methods="GET")
     */
    public function listAgencias(AgenciaRepository $agenciaRepository): Response
    {
        return $this->render('agencias/agencia/index.html.twig', ['agencias' => $agenciaRepository->findAll()]);
    }

    /**
     * @Route("/nova", name="new-agencia", methods="GET|POST")
     */
    public function newAgencia(Request $request): Response
    {
        $agencium = new Agencia();
        $form = $this->createForm(AgenciaType::class, $agencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('agencia');
            $em->persist($agencium);
            $em->flush();

            return $this->redirectToRoute('list-agencias');
        }

        return $this->render('agencias/agencia/new.html.twig', [
            'agencium' => $agencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="agencia_agencia_show", methods="GET")
     */
    public function show(Agencia $agencium): Response
    {
        return $this->render('agencias_agencia_show.html.twig', ['agencium' => $agencium]);
    }

    /**
     * @Route("/{id}/editar", name="edit-agencia", methods="GET|POST")
     */
    public function edit(Request $request, Agencia $agencium): Response
    {
        $form = $this->createForm(AgenciaType::class, $agencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('list-agencias', ['id' => $agencium->getId()]);
        }

        return $this->render('agencias/agencia/edit.html.twig', [
            'agencium' => $agencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete-agencia", methods="DELETE")
     */
    public function deleteAgencia(Request $request, Agencia $agencium): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$agencium->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('agencias_agencia_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($agencium);
        $em->flush();

        return $this->redirectToRoute('agencias_agencia_index');
    }

    /**
     * @Route("/{id}", name="change-status-agencia", methods="PUT")
     */
    public function changeStatus(Request $request, Agencia $agencia): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('agencia.change-status.title', ['%name%' => $agencia->getNome()]);
        if ($this->isCsrfTokenValid('put'.$agencia->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('agencia');
            $agencia->setIsActive(!$agencia->getIsActive());
            $em->persist($agencia);
            $em->flush();
            $message = $this->get('translator')->trans('agencia.change-status.success', ['%name%'=> $agencia->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }
}
