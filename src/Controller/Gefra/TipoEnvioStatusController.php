<?php

namespace App\Controller\Gefra;

use App\Entity\Gefra\TipoEnvioStatus;
use App\Form\Gefra\TipoEnvioStatusType;
use App\Repository\Gefra\TipoEnvioStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/gefra/tipo-envio-status")
 */
class TipoEnvioStatusController extends Controller
{

    /**
     * @Route("/", name="gefra_tipo_envio_status_index", methods="GET")
     */
    public function index(TipoEnvioStatusRepository $tipoEnvioStatusRepository): Response
    {
        return $this->render('gefra/tipo_envio_status/index.html.twig', ['tipo_envio_statuses' => $tipoEnvioStatusRepository->findAll()]);
    }

    /**
     * @Route("/novo", name="gefra_tipo_envio_status_new", methods="GET|POST")
     */
    public function newTipoEnvioStatus(Request $request): Response
    {
        $tipoEnvioStatus = new TipoEnvioStatus();
        $form = $this->createForm(TipoEnvioStatusType::class, $tipoEnvioStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('gefra');
            $em->persist($tipoEnvioStatus);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('gefra_tipo_envio_status_index');
        }

        return $this->render('gefra/tipo_envio_status/new.html.twig', [
            'tipo_envio_status' => $tipoEnvioStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_tipo_envio_status_show", methods="GET")
     */
    public function show(TipoEnvioStatus $tipoEnvioStatus): Response
    {
        return $this->render('gefra_tipo_envio_status/show.html.twig', ['tipo_envio_status' => $tipoEnvioStatus]);
    }

    /**
     * @Route("/{id}/editar", name="gefra_tipo_envio_status_edit", methods="GET|POST")
     */
    public function edit(Request $request, TipoEnvioStatus $tipoEnvioStatus): Response
    {
        $form = $this->createForm(TipoEnvioStatusType::class, $tipoEnvioStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('gefra')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('gefra_tipo_envio_status_index', ['id' => $tipoEnvioStatus->getId()]);
        }

        return $this->render('gefra/tipo_envio_status/edit.html.twig', [
            'tipo_envio_status' => $tipoEnvioStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_tipo_envio_status_delete", methods="DELETE")
     */
    public function delete(Request $request, TipoEnvioStatus $tipoEnvioStatus): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tipoEnvioStatus->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tipoEnvioStatus);
            $em->flush();
        }

        return $this->redirectToRoute('gefra_tipo_envio_status_index');
    }
}
