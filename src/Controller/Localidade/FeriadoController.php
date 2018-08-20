<?php

namespace App\Controller\Localidade;

use App\Entity\Localidade\Feriado;
use App\Entity\Localidade\TipoFeriado;
use App\Form\Localidade\FeriadoType;
use App\Form\Localidade\TipoFeriadoType;
use App\Repository\Localidade\FeriadoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/localidade/feriado")
 */
class FeriadoController extends Controller
{
    /**
     * @Route("/", name="localidade_feriado_index", methods="GET")
     */
    public function index(FeriadoRepository $feriadoRepository): Response
    {
        return $this->render('localidade/feriado/index.html.twig', ['feriados' => $feriadoRepository->findAll()]);
    }

    /**
     * @Route("/novo", name="localidade_feriado_new", methods="GET|POST")
     */
    public function _new(Request $request): Response
    {
        $feriado = new Feriado();
        $form = $this->createForm(FeriadoType::class, $feriado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->persist($feriado);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');

            return $this->redirectToRoute('localidade_feriado_index');
        }

        return $this->render('localidade/feriado/new.html.twig', [
            'feriado' => $feriado,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="localidade_feriado_show", methods="GET")
     */
    public function show(Feriado $feriado): Response
    {
        return $this->render('localidade_feriado/show.html.twig', ['feriado' => $feriado]);
    }

    /**
     * @Route("/{id}/editar", name="localidade_feriado_edit", methods="GET|POST")
     */
    public function edit(Request $request, Feriado $feriado): Response
    {
        $form = $this->createForm(FeriadoType::class, $feriado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('locais')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('localidade_feriado_index');
        }

        return $this->render('localidade/feriado/edit.html.twig', [
            'feriado' => $feriado,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="localidade_feriado_delete", methods="DELETE")
     */
    public function delete(Request $request, Feriado $feriado): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feriado->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->remove($feriado);
            $em->flush();
        }

        return $this->redirectToRoute('localidade_feriado_index');
    }

    /**
     * @Route("/tipo-feriado/novo", name="localidade_tipoferiado_new", methods="POST")
     */
    public function newTipoFeriado(Request $request): Response
    {
        $tipoferiado = new TipoFeriado();
        $tipoferiadoform = $this->createForm(
            TipoFeriadoType::class,
            $tipoferiado,
            array('action' => $this->generateUrl('localidade_tipoferiado_new'))
        );
        $tipoferiadoform->handleRequest($request);

        if ($tipoferiadoform->isSubmitted() && $tipoferiadoform->isValid()) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->persist($tipoferiado);
            $em->flush();
            $this->addFlash('success', 'localidade.tipoferiado.flash.new');
            return $this->redirectToRoute('localidade_feriado_new');
        }

        return $this->render('localidade/feriado/_tipo_feriado_form.html.twig',
            ['tipoferiado' => $tipoferiado, 'form' => $tipoferiadoform->createView(),]);
    }
}
