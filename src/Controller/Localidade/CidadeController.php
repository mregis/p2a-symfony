<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 04/07/2018
 * Time: 12:57
 */

namespace App\Controller\Localidade;

use App\Entity\Localidade\Cidade;
use App\Form\Localidade\CidadeType;
use App\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/localidade/cidade")
 */
class CidadeController extends Controller
{
    /**
     * @Route("/", name="localidade_cidade_index", methods="GET")
     */
    public function listCidade(): Response
    {
        return $this->render('localidade/cidade/index.html.twig');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="localidade_cidade_list_json", methods="GET|POST")
     */
    public function getCidades(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column']; // somente uma coluna para ordenação aqui
        $orderColumn = array('c.nome', 'c.abreviacao', 'c.uf', 'c.codigo')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        $cidade_repo = $this->getDoctrine()
            ->getManager('locais')
            ->getRepository(Cidade::class);
        $qb = $cidade_repo->createQueryBuilder('c')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);

        if ($search_value != null) {
            $search_value = StringUtils::slugify($search_value);
            $qb->orWhere(
                $qb->expr()->like('c.codigo', '?1'),
                $qb->expr()->like('c.canonical_name', '?1')
            )->setParameters([1 => '%' . $search_value . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');

        $data = [];
        /* @var $cidade Cidade */
        foreach ($paginator as $cidade) {
            $d['cidade'] = unserialize($cidade->serialize());
            $d['buttons'] = 'BUTTONS';
            $d['deleteToken'] = $tokenProvider->getToken('delete' . $cidade->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $cidade->getId())->getValue();
            $d['changetitle'] = $this->get('translator')
                ->trans('localidade.change-status.title', ['%name%' => $cidade->getNome()]);
            $d['deltitle'] = $this->get('translator')
                ->trans('localidade.delete.title', ['%name%' => $cidade->getNome()]);
            $d['editUrl'] = $this->generateUrl('localidade_cidade_edit', ['id' => $cidade->getId()]);
            $data[] = $d;
        }

        $recordsTotal = count($paginator);

        $response = array("draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => $data,
        );

        return $this->json($response, Response::HTTP_OK);

    }

    /**
     * @Route("/novo", name="localidade_cidade_new", methods="GET|POST")
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
            return $this->redirectToRoute('localidade_cidade_index');
        }

        return $this->render('localidade/cidade/new.html.twig', [
            'cidade' => $cidade,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/cadastro-lote", name="localidade_cidade_loadfile", methods="GET|POST")
     */
    public function newCidadeBulk(Request $request): Response
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
                $uf_repo = $em->getRepository(UF::class);
                $cidade_repo = $em->getRepository(Cidade::class);
                set_time_limit(0); // Avoiding Maximum Execution Timeout
                try {
                    foreach ($data as $k => $entry) {
                        if (!isset($entry['UF'], $entry['NOME'])) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. Verifique se os cabeçalhos estão corretos " .
                                    "[UF, NOME, ABREVIACAO[,CODIGO][,ATIVO].", $k + 2)
                            );
                        }
                        if (!$uf = $uf_repo->findBySigla($entry['UF'])) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. %s não é uma UF válida.", $k + 2, $entry['UF'])
                            );
                        }

                        if (!isset($entry['ABREVIACAO'])) {
                            $entry['ABREVIACAO'] = $entry['NOME'];
                        }
                        $entry['ABREVIACAO'] = substr($entry['ABREVIACAO'], 0, 20);
                        if (!$cidade = $cidade_repo->findOneBy(['nome' => $entry['NOME'], 'uf' => $entry['UF']])) {
                            // Cidade já existente, informações serão atualizadas
                            $cidade = new Cidade();
                            $cidade->setUf($uf)
                                ->setNome($entry['NOME']);
                        }

                        $cidade->setAbreviacao($entry['ABREVIACAO']);
                        if (isset($entry['CODIGO'])) {
                            $cidade->setCodIBGE($entry['CODIGO']);
                        }
                        if (isset($entry['ATIVO'])) {
                            $uf->setAtivo($entry['ATIVO']);
                        }

                        $em->persist($cidade);
                    }
                    $em->flush();
                } catch (Exception $e) {
                    throw new \InvalidArgumentException($e->getMessage());
                }

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('localidade_cidade_index');
            } catch(Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('localidade/cidade/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/{id}", name="localidade_cidade_show", methods="GET")
     */
    public function showCidade(Cidade $cidade): Response
    {
        return $this->render('localidade/cidade/show.html.twig', ['cidade' => $cidade]);
    }

    /**
     * @Route("/cidade/{id}/editar", name="localidade_cidade_edit", methods="GET|POST")
     */
    public function editCidade(Request $request, Cidade $cidade): Response
    {
        $form = $this->createForm(CidadeType::class, $cidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('locais')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('localidade_cidade_index');
        }

        return $this->render('localidade/cidade/edit.html.twig', [
            'cidade' => $cidade,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="localidade_cidade_delete", methods="DELETE")
     */
    public function deleteCidade(Request $request, Cidade $cidade): JsonResponse
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('localidade.delete.title', ['%name%' => $cidade->getNome()]);
        if ($this->isCsrfTokenValid('delete'.$cidade->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('locais');
            $em->remove($cidade);
            $em->flush();
            $message = $this->get('translator')->trans('flash.success.delete', ['%name%'=> $cidade->getNome()]);
            $statusMode = 'success';
        }
        return $this->json(
            array('message' => $message, 'status' => $statusMode, 'title' => $title),
            Response::HTTP_OK);

    }

    /**
     * @Route("/{id}", name="localidade_cidade_changestatus", methods="PUT")
     */
    public function changeStatusCidade(Request $request, Cidade $cidade): JsonResponse
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

}