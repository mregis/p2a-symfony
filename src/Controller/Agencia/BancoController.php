<?php

namespace App\Controller\Agencia;

use App\Entity\Agencia\Banco;
use App\Form\Agencia\BancoType;
use App\Repository\Agencia\BancoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/agencias/banco")
 */
class BancoController extends Controller
{
    /**
     * @Route("/", name="list-bancos", methods="GET")
     */
    public function index(): Response
    {
        $bancos = $this->getDoctrine()
            ->getRepository(Banco::class, 'agencia')
            ->findAll();

        return $this->render('agencias/banco/index.html.twig', ['bancos' => $bancos]);
    }

    /**
     * @Route("/novo", name="new-banco", methods="GET|POST")
     */
    public function newBanco(Request $request): Response
    {
        $banco = new Banco();
        $form = $this->createForm(BancoType::class, $banco);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('agencia');
            $banco->setCnpj(preg_replace('#\D#', '', $banco->getCnpj()));
            $em->persist($banco);
            $em->flush();
            $this->addFlash('success', 'banco.flash.new-success');
            return $this->redirectToRoute('list-bancos');
        }

        return $this->render('agencias/banco/new.html.twig', [
            'banco' => $banco,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="agencia_banco_show", methods="GET")
     */
    public function show(Banco $banco): Response
    {
        return $this->render('agencias/banco/show.html.twig', ['banco' => $banco]);
    }

    /**
     * @Route("/{id}/excluir", name="del-banco", methods="DELETE")
     */
    public function delete(Request $request, Banco $banco): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$banco->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('list-bancos');
        }

        $em = $this->getDoctrine()->getManager('agencia');
        $em->remove($banco);
        $em->flush();

        return $this->redirectToRoute('list-bancos');
    }

    /**
     * @Route("/{id}/editar", name="edit-banco", methods="GET|POST")
     */
    public function edit(Request $request, Banco $banco, AuthorizationCheckerInterface $authChecker): Response
    {
        $form = $this->createForm(BancoType::class, $banco);
        // Permitir mudar estado apenas para o SUPERADMIN
        if (false === $authChecker->isGranted('ROLE_SUPERADMIN')) {
            $isActiveField = $form->get('is_active');
            $attrs = $isActiveField->getConfig()->getOptions();
            $attrs['attr']['readonly'] ='readonly';
            $attrs['disabled'] ='disabled';

            $form->remove($isActiveField->getName());
            $form->add($isActiveField->getName(),
                get_class($isActiveField->getConfig()->getType()->getInnerType()), $attrs);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $banco->setCnpj(preg_replace('#\D#', '', $banco->getCnpj()));
            $this->getDoctrine()->getManager('agencia')->flush();
            $this->addFlash('success', 'banco.flash.edit-success');
            return $this->redirectToRoute('edit-banco', ['id' => $banco->getId()]);
        }

        return $this->render('agencias/banco/edit.html.twig', [
            'banco' => $banco,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="change-status-banco", methods="PUT")
     */
    public function changeStatus(Request $request, Banco $banco): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('banco.change-status.title', ['%banco%' => $banco->getNome()]);
        if ($this->isCsrfTokenValid('put'.$banco->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('agencia');
            $banco->setIsActive(!$banco->getIsActive());
            $em->persist($banco);
            $em->flush();
            $message = $this->get('translator')->trans('banco.change-status.success', ['%banco%'=> $banco->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }
}
