<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 18/07/2018
 * Time: 17:20
 */

namespace App\Controller\Gefra;

use App\Entity\Agencia\Banco;
use App\Entity\Gefra\Juncao;
use App\Entity\Localidade\UF;
use App\Form\Gefra\JuncaoType;
use App\Form\Type\BulkRegistryType;
use App\Util\StringUtils;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class JuncaoController
 * @package App\Controller\Gefra
 * @Route("/gefra/juncao")
 */
class JuncaoController extends Controller
{
    /**
     * @Route("/", name="gefra_juncao_index", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('gefra/juncao/index.html.twig');
    }

    /**
     * @Route("/nova", name="gefra_juncao_new", methods="GET|POST")
     */
    public function newJuncao(Request $request): Response
    {
        $juncao = new Juncao();
        $form = $this->createForm(JuncaoType::class, $juncao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager('gefra');
                $em->persist($juncao);
                $em->flush();
                $this->addFlash('success', 'flash.success.new');
                return $this->redirectToRoute('gefra_juncao_index');
        }

        return $this->render('gefra/juncao/new.html.twig', [
            'juncao' => $juncao,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/cadastro-lote", name="gefra_juncao_loadfile", methods="GET|POST")
     */
    public function newJuncaoBulk(Request $request): Response
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
                $em = $this->getDoctrine()->getManager('gefra');
                $uf_repo = $this->getDoctrine()->getManager('locais')->getRepository(UF::class);
                $juncao_repo = $em->getRepository(Juncao::class);
                $banco_repo = $this->getDoctrine()->getManager('agencia')->getRepository(Banco::class);
                set_time_limit(0); // Avoiding Maximum Execution Timeout
                $batchSize = max((int) $this->container->getParameter('app.bulk.batchsize'), 50);
                $j = 0; // counter

                foreach ($data as $k => $entry) {
                    if (!isset($entry['BANCO'],$entry['NOME'], $entry['CODIGO'],
                        $entry['CIDADE'], $entry['UF'])) {
                        throw new \Exception(
                            sprintf("Erro na linha %d. Verifique se os cabeçalhos estão corretos " .
                                "[BANCO,NOME,CODIGO,CIDADE,UF[,ATIVO].", $k + 2)
                        );
                    }

                    if (!$banco = $banco_repo->findOneByCodigo($entry['BANCO'])) {
                        throw new \Exception(
                            sprintf("Erro na linha %d. [%s] não é código de BANCO válido.", $k + 2, $entry['BANCO'])
                        );
                    }

                    if (!$uf = $uf_repo->findBySigla($entry['UF'])) {
                        throw new \Exception(
                            sprintf("Erro na linha %d. [%s] não é uma UF válida.", $k + 2, $entry['UF'])
                        );
                    }

                    if (!$juncao = $juncao_repo->findOneBy(['codigo' => $entry['CODIGO'],
                        'banco' => $banco->getCodigo()])) {
                        // Nova Agência
                        $juncao = new Juncao();
                        $juncao->setCodigo($entry['CODIGO'])
                            ->setBanco($banco->getCodigo());

                    } // else
                    // Junção já existente, informações serão atualizadas

                    // Detecting encoding and converting to UTF-8 before persist into database
                    try {
                        // campos texto
                        foreach (['NOME','CIDADE'] as $field) {
                            if (isset($entry[$field])) {
                                $current_encoding = mb_detect_encoding(
                                    $entry[$field], 'UTF-8, ISO-8859-1, ASCII'
                                );
                                $data = mb_convert_encoding($entry[$field], 'UTF-8', $current_encoding);
                                $data = iconv('UTF-8', "UTF-8//TRANSLIT ", $data);
                                $entry[$field] = trim($data);
                            }
                        }

                        // campos obrigatórios
                        foreach (['NOME','CIDADE'] as $field) {
                            if (isset($entry[$field]) && $entry[$field] != '') {
                                $juncao->{'set' . ucfirst(strtolower($field))}($entry[$field]);
                            } else {
                                throw new \Exception(
                                    sprintf("Erro na linha %d. [%s] não pode ficar em branco.",
                                        $k + 2, $field)
                                );
                            }
                        }

                    } catch (Exception $e) {
                        throw new \Exception(sprintf('Error on line %d, field %s. more -> ' .
                            $e->getMessage(), $k +2, $field));
                    }
                    $juncao->setUf($uf->getSigla());

                    if (isset($entry['ATIVO'])) {
                        $juncao->setIsActive($entry['ATIVO']);
                    }

                    $em->persist($juncao);
                    if ($j++ > $batchSize)
                    {
                        $em->flush();
                        $j = 0;
                    }
                    echo "\n"; // Avoiding Browser Timeout
                }
                $em->flush();

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('gefra_juncao_index');
            } catch(Exception $e) {
                $error = new FormError('Arquivo inválido! ' . $e->getMessage());
            }
        }

        return $this->render('gefra/juncao/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/edit/{id}", name="gefra_juncao_edit", methods="GET|POST")
     */
    public function edit(Request $request, Juncao $juncao): Response
    {
        $form = $this->createForm(JuncaoType::class, $juncao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('gefra')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('gefra_juncao_index', ['id' => $juncao->getId()]);
        }

        return $this->render('gefra/juncao/edit.html.twig', [
            'juncao' => $juncao,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_juncao_delete", methods="DELETE")
     */
    public function deleteJuncao(Request $request, Juncao $juncao): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$juncao->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('gefra_juncao_list');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($juncao);
        $em->flush();

        return $this->redirectToRoute('gefra_juncao_list');
    }

    /**
     * @Route("/{id}", name="gefra_juncao_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Juncao $juncao): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('gefra.juncao.changestatus.title', ['%name%' => $juncao->getNome()]);
        if ($this->isCsrfTokenValid('put'.$juncao->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('gefra');
            $juncao->setIsActive(!$juncao->getIsActive());
            $em->persist($juncao);
            $em->flush();
            $message = $this->get('translator')->trans('gefra.juncao.changestatus.success', ['%name%'=> $juncao->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(
            array('message' => $message, 'status' => $statusMode, 'title' => $title),
            Response::HTTP_OK);
    }


    /**
     * @Route("/{id}", name="gefra_juncao_show", methods="GET")
     */
    public function show(Juncao $juncao): Response
    {
        return $this->render('gefra_juncao_show.html.twig', ['juncao' => $juncao]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="gefra_juncao_list_json", methods="GET|POST")
     */
    public function getJuncoes(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('a.banco', 'a.codigo','a.nome', 'a.cidade', 'a.uf')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        $cidade_repo = $this->getDoctrine()
            ->getManager('gefra')
            ->getRepository(Juncao::class);
        $qb = $cidade_repo->createQueryBuilder('a')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);


        if ($search_value != null) {
            $search_value = StringUtils::slugify($search_value);
            $qb->orWhere(
                $qb->expr()->like('a.codigo', '?1'),
                $qb->expr()->like('a.canonical_name', '?1'),
                $qb->expr()->like('a.canonical_city', '?1')
            )->setParameters([1 => '%' . $search_value . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');

        $data = [];
        $banco_repo = $this->getDoctrine()->getManager('agencia')->getRepository(Banco::class);
        /* @var $juncao Juncao */
        foreach ($paginator as $juncao) {
            $banco = $banco_repo->findOneByCodigo($juncao->getBanco()); // Lazy Load
            $d['juncao'] = unserialize($juncao->serialize());
            $d['juncao']['banco'] = unserialize($banco->serialize());
            $d['buttons'] = 'BUTTONS';
            $d['deleteToken'] = $tokenProvider->getToken('delete' . $juncao->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $juncao->getId())->getValue();
            $d['changetitle'] = $this->get('translator')
                ->trans('gefra.juncao.changestatus.title', ['%name%' => $juncao->getNome()]);
            $d['deltitle'] = $this->get('translator')
                ->trans('gefra.juncao.delete.title', ['%name%' => $juncao->getNome()]);
            $d['editUrl'] = $this->generateUrl('gefra_juncao_edit', ['id' => $juncao->getId()]);
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

}