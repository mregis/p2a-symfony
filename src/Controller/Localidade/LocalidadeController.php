<?php

namespace App\Controller\Localidade;

use App\Entity\Localidade\Cidade;
use App\Entity\Localidade\Regiao;
use App\Entity\Localidade\UF;
use App\Form\Localidade\CidadeType;
use App\Form\Localidade\RegiaoType;
use App\Form\Localidade\UFType;
use App\Form\Type\BulkRegistryType;
use PHPUnit\Runner\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/localidade")
 */
class LocalidadeController extends Controller
{
    /**
     * @Route("/", name="localidade", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('localidade/index.html.twig');
    }

    ######## REGIAO ROUTES
    /**
     * @Route("/regiao", name="list-regiao", methods="GET")
     */
    public function listRegiao(): Response
    {
        $regioes = $this->getDoctrine()
            ->getRepository(Regiao::class, 'locais')
            ->findAll();
        return $this->render('localidade/regiao/list.html.twig', ['regioes' => $regioes]);
    }

    /**
     * @Route("/regiao/novo", name="new-regiao", methods="GET|POST")
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
     * @Route("/regiao/{id}", name="show-regiao", methods="GET")
     */
    public function showRegiao(Regiao $regiao): Response
    {
        return $this->render('localidade/regiao/show.html.twig', ['regiao' => $regiao]);
    }

    /**
     * @Route("/regiao/{id}/editar", name="edit-regiao", methods="GET|POST")
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
     * @Route("/regiao/{id}", name="delete-regiao", methods="DELETE")
     */
    public function deleteRegiao(Request $request, Regiao $regiao): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$regiao->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('list-regiao');
        }

        $em = $this->getDoctrine()->getManager('locais');
        $em->remove($regiao);
        $em->flush();
        $this->addFlash('success', 'regiao.flash.delete-success');
        return $this->redirectToRoute('list-regiao');
    }

    /**
     * @Route("/regiao/{id}", name="change-status-regiao", methods="PUT")
     */
    public function changeStatusRegiao(Request $request, Regiao $regiao): Response
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

    ######## END - REGIAO ROUTES

    ######## UF ROUTES
    /**
     * @Route("/uf", name="list-uf", methods="GET")
     */
    public function listUF(): Response
    {
        $ufs = $this->getDoctrine()
            ->getRepository(UF::class, 'locais')
            ->findAll();
        return $this->render('localidade/uf/list.html.twig', ['ufs' => $ufs]);
    }

    /**
     * @Route("/uf/novo", name="new-uf", methods="GET|POST")
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
            return $this->redirectToRoute('list-uf');
        }

        return $this->render('localidade/uf/new.html.twig', [
            'uf' => $uf,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/uf/cadastro-uf-lote", name="new-uf-bulk", methods="GET|POST")
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
                                sprintf("Erro na linha %d. Verifique se os cabeçalhos estão corretos " .
                                    "[REGIAO, NOME, SIGLA[, ATIVO]].", $k + 2)
                            );
                        }
                        if (!$regiao = $regiao_repo->findOneBy(array('sigla' => $entry['REGIAO']))) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. %s não é uma região válida.", $k + 2, $entry['REGIAO'])
                            );
                        }
                        $uf = new UF();
                        $uf->setRegiao($regiao)
                            ->setNome($entry['NOME'])
                            ->setSigla($entry['SIGLA']);
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
                return $this->redirectToRoute('list-uf');
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
     * @return Response
     * @Route("uf/arquivo-modelo/{source}", name="sample-file")
     */
    public function downloadSampleCVS(Request $request, $source): Response
    {
        $filename = $this->getParameter('app.samples.dir');
        $outputname = 'filaname';
        switch ($source) {
            case 'uf':
                $filename .= 'uf.sample.csv';
                $outputname = $this->get('translator')->trans('localidade.uf.sample-filename');
                break;
            case '
            ':
                $filename .= 'local.sample.csv';
                $outputname = $this->get('translator')->trans('localidade.cidade.sample-filename');
                break;
            default:
                throw new NotFoundHttpException();
        }
        return $this->file($filename, $outputname . '.csv');
    }

    /**
     * @Route("/uf/{id}", name="show-uf", methods="GET")
     */
    public function showUf(UF $uf): Response
    {
        return $this->render('localidade/uf/show.html.twig', ['uf' => $uf]);
    }

    /**
     * @Route("/uf/{id}/editar", name="edit-uf", methods="GET|POST")
     */
    public function editUF(Request $request, UF $uf): Response
    {
        $form = $this->createForm(UFType::class, $uf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('locais')->flush();
            $this->addFlash('success', 'uf.flash.edit-success');
            return $this->redirectToRoute('list-uf', ['id' => $uf->getId()]);
        }

        return $this->render('localidade/uf/edit.html.twig', [
            'uf' => $uf,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/uf/{id}", name="delete-uf", methods="DELETE")
     */
    public function deleteUF(Request $request, UF $uf): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$uf->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('list-uf');
        }

        $em = $this->getDoctrine()->getManager('locais');
        $em->remove($uf);
        $em->flush();
        $this->addFlash('success', 'flash.success.delete');

        return $this->redirectToRoute('list-uf');
    }

    /**
     * @Route("/uf/{id}", name="change-status-uf", methods="PUT")
     */
    public function changeStatusUF(Request $request, UF $uf): Response
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


    ######## END - UF ROUTES

    ######## CIDADE ROUTES
    /**
     * @Route("/cidade", name="list-cidade", methods="GET")
     */
    public function listCidade(): Response
    {
        $cidades = $this->getDoctrine()
            ->getRepository(Cidade::class, 'locais')
            ->findAll();
        return $this->render('localidade/cidade/list.html.twig', ['cidades' => $cidades]);
    }

    /**
     * @Route("/cidade/novo", name="new-cidade", methods="GET|POST")
     */
    public function newCidade(Request $request): Response
    {
        $cidade = new Cidade();
        $form = $this->createForm(CidadeType::class, $cidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->persist($cidade);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('list-cidade');
        }

        return $this->render('localidade/cidade/new.html.twig', [
            'cidade' => $cidade,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cidade/{id}", name="show-cidade", methods="GET")
     */
    public function showCidade(Cidade $cidade): Response
    {
        return $this->render('localidade/cidade/show.html.twig', ['cidade' => $cidade]);
    }

    /**
     * @Route("/cidade/{id}/editar", name="edit-cidade", methods="GET|POST")
     */
    public function editCidade(Request $request, Cidade $cidade): Response
    {
        $form = $this->createForm(CidadeType::class, $cidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('locais')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('list-cidade', ['id' => $cidade->getId()]);
        }

        return $this->render('localidade/cidade/edit.html.twig', [
            'cidade' => $cidade,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cidade/{id}", name="delete-cidade", methods="DELETE")
     */
    public function deleteCidade(Request $request, Cidade $cidade): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$cidade->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('list-cidade');
        }

        $em = $this->getDoctrine()->getManager('locais');
        $em->remove($cidade);
        $em->flush();
        $this->addFlash('success', 'cidade.flash.delete-success');
        return $this->redirectToRoute('list-cidade');
    }

    /**
     * @Route("/cidade/{id}", name="change-status-cidade", methods="PUT")
     */
    public function changeStatusCidade(Request $request, Cidade $cidade): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('localidade.change-status.title', ['%name%' => $cidade->getNome()]);
        if ($this->isCsrfTokenValid('put'.$cidade->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $cidade->setAtivo(!$cidade->isAtivo());
            $em->persist($cidade);
            $em->flush();
            $message = $this->get('translator')->trans('flash.success.change-status', ['%name%'=> $cidade->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

    ######## END - CIDADE ROUTES

}
