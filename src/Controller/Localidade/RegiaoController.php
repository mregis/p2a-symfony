<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 04/07/2018
 * Time: 12:58
 */

namespace App\Controller\Localidade;

use App\Entity\Localidade\Regiao;
use App\Form\Localidade\RegiaoType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class RegiaoController
 * @package Controller\Localidade
 *
 * @Route("/localidade/regiao")
 */
class RegiaoController extends Controller
{
    /**
     * @Route("/", name="localidade_regiao_index", methods="GET")
     */
    public function listRegiao(): Response
    {
        $regioes = $this->getDoctrine()
            ->getRepository(Regiao::class, 'locais')
            ->findAll();
        return $this->render('localidade/regiao/list.html.twig', ['regioes' => $regioes]);
    }

    /**
     * @Route("/novo", name="localidade_regiao_new", methods="GET|POST")
     */
    public function newRegiao(Request $request): Response
    {
        $regiao = new Regiao();
        $form = $this->createForm(RegiaoType::class, $regiao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->persist($regiao);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('list-regiao');
        }

        return $this->render('localidade/regiao/new.html.twig', [
            'regiao' => $regiao,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="localidade_regiao_show", methods="GET")
     */
    public function showRegiao(Regiao $regiao): Response
    {
        return $this->render('localidade/regiao/show.html.twig', ['regiao' => $regiao]);
    }

    /**
     * @Route("/{id}/editar", name="localidade_regiao_edit", methods="GET|POST")
     */
    public function editRegiao(Request $request, Regiao $regiao): Response
    {
        $form = $this->createForm(RegiaoType::class, $regiao);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('locais')->flush();
            $this->addFlash('success', 'regiao.flash.edit-success');
            return $this->redirectToRoute('list-regiao', ['id' => $regiao->getId()]);
        }

        return $this->render('localidade/regiao/edit.html.twig', [
            'regiao' => $regiao,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="localidade_regiao_delete", methods="DELETE")
     */
    public function deleteRegiao(Request $request, Regiao $regiao): JsonResponse
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('localidade.delete.title', ['%name%' => $regiao->getNome()]);
        if ($this->isCsrfTokenValid('delete'.$regiao->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->remove($regiao);
            $em->flush();
            $message = $this->get('translator')->trans('flash.success.delete', ['%name%'=> $regiao->getNome()]);
            $statusMode = 'success';
        }
        return $this->json(
            array('message' => $message, 'status' => $statusMode, 'title' => $title),
            Response::HTTP_OK);

    }

    /**
     * @Route("/{id}", name="localidade_regiao_changestatus", methods="PUT")
     */
    public function changeStatusRegiao(Request $request, Regiao $regiao): JsonResponse
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('localidade.regiao.change-status.title', ['%banco%' => $regiao->getNome()]);
        if ($this->isCsrfTokenValid('put'.$regiao->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $regiao->setAtivo(!$regiao->isAtivo());
            $em->persist($regiao);
            $em->flush();
            $message = $this->get('translator')->trans('banco.change-status.success', ['%banco%'=> $regiao->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

}