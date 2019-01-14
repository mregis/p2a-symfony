<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 04/07/2018
 * Time: 13:01
 */

namespace App\Controller\Localidade;

use App\Entity\Localidade\Regiao;
use App\Entity\Localidade\UF;
use App\Form\Localidade\UFType;
use App\Form\Type\BulkRegistryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UFController
 * @package Controller\Localidade
 *
 *  * @Route("/localidade/uf")
 */
class UFController extends AbstractController
{
    /**
     * @Route("/", name="localidade_uf_index", methods="GET")
     */
    public function listUF(): Response
    {
        $ufs = $this->getDoctrine()
            ->getRepository(UF::class, 'locais')
            ->findAll();
        return $this->render('localidade/uf/list.html.twig', ['ufs' => $ufs]);
    }

    /**
     * @Route("/novo", name="localidade_uf_new", methods="GET|POST")
     */
    public function newUf(Request $request): Response
    {
        $uf = new UF();
        $form = $this->createForm(UFType::class, $uf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->persist($uf);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('localidade_uf_index');
        }

        return $this->render('localidade/uf/new.html.twig', [
            'uf' => $uf,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/cadastro-lote", name="localidade_uf_loadfile", methods="GET|POST")
     */
    public function newUFBulk(Request $request): Response
    {
        $error = null;
        $form = $this->createForm(BulkRegistryType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
                $file = $form->get('registry')->getData();
                // Validando o arquivo
                $serializer = $this->get('serializer');
                $data = $serializer->decode(file_get_contents($file->getPathname()), 'csv');
                $em = $this->getDoctrine()->getManager('locais');
                $regiao_repo = $em->getRepository(Regiao::class);
                try {
                    // Desprezar primeira linha (cabeçalhos)
                    foreach ($data as $k => $entry) {
                        if (!isset($entry['REGIAO'], $entry['NOME'], $entry['SIGLA'])) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. Verifique as informações e " .
                                    "se os cabeçalhos [REGIAO, NOME, SIGLA[, ATIVO]] " .
                                    "estão corretos.", $k + 2)
                            );
                        }
                        if (!$regiao = $regiao_repo->findOneBy(array('sigla' => $entry['REGIAO']))) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. %s não é uma região válida.", $k + 2, $entry['REGIAO'])
                            );
                        }
                        // Verificando se já não é um UF existente
                        if (!$uf = $em->getRepository(UF::class)->findBySigla($entry['SIGLA'])) {
                            $uf = new UF();
                            $uf->setSigla($entry['SIGLA']);
                        }
                        $uf->setRegiao($regiao)
                            ->setNome($entry['NOME']);

                        if (isset($entry['ATIVO'])) {
                            $uf->setAtivo($entry['ATIVO']);
                        }

                        $em->persist($uf);
                    }
                    $em->flush();
                } catch (Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('localidade_uf_index');
            } catch(Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('localidade/uf/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/{id}", name="localidade_uf_show", methods="GET")
     */
    public function showUf(UF $uf): Response
    {
        return $this->render('localidade/uf/show.html.twig', ['uf' => $uf]);
    }

    /**
     * @Route("/{id}/editar", name="localidade_uf_edit", methods="GET|POST")
     */
    public function editUF(Request $request, UF $uf): Response
    {
        $form = $this->createForm(UFType::class, $uf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('locais')->flush();
            $this->addFlash('success', 'uf.flash.edit-success');
            return $this->redirectToRoute('localidade_uf_index');
        }

        return $this->render('localidade/uf/edit.html.twig', [
            'uf' => $uf,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="localidade_uf_delete", methods="DELETE")
     */
    public function deleteUF(Request $request, UF $uf): JsonResponse
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('localidade.delete.title', ['%name%' => $uf->getNome()]);
        if ($this->isCsrfTokenValid('delete'.$uf->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->remove($uf);
            $em->flush();
            $message = $this->get('translator')->trans('flash.success.delete', ['%name%'=> $uf->getNome()]);
            $statusMode = 'success';
        }
        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="localidade_uf_changestatus", methods="PUT")
     */
    public function changeStatusUF(Request $request, UF $uf): JsonResponse
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('localidade.change-status.title', ['%name%' => $uf->getNome()]);
        if ($this->isCsrfTokenValid('put'.$uf->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $uf->setAtivo(!$uf->isAtivo());
            $em->persist($uf);
            $em->flush();
            $message = $this->get('translator')->trans('flash.success.change-status', ['%name%'=> $uf->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

}