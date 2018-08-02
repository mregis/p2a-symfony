<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 18/07/2018
 * Time: 17:20
 */

namespace App\Controller\Gefra;

use App\Entity\Agencia\Banco;
use App\Entity\Gefra\Envio;
use App\Entity\Localidade\UF;
use App\Form\Gefra\EnvioType;
use App\Form\Type\BulkRegistryType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class EnvioController
 * @package App\Controller\Gefra
 * @Route("/gefra/envio")
 */
class EnvioController extends Controller
{
    /**
     * @Route("/", name="gefra_envio_index", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('gefra/envio/index.html.twig');
    }

    /**
     * @Route("/novo", name="gefra_envio_new", methods="GET|POST")
     */
    public function newEnvio(Request $request): Response
    {
        $envio = new Envio();
        $form = $this->createForm(EnvioType::class, $envio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager('gefra');
                $em->persist($envio);
                $em->flush();
                $this->addFlash('success', 'flash.success.new');
                return $this->redirectToRoute('gefra_envio_index');
        }

        return $this->render('gefra/envio/new.html.twig', [
            'juncao' => $envio,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/carregar-envios", name="gefra_envio_load_file", methods="GET|POST")
     */
    public function loadEnvioFile(Request $request): Response
    {
        $error = null;
        $form = $this->createForm(BulkRegistryType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {

                /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
                $file = $form->get('registry')->getData();
                $crawler = new Crawler(file_get_contents($file->getPathname()));
                $worksheet_table = $crawler->filterXPath('//default:Workbook/Worksheet/Table');
                var_dump($worksheet_table);

                $em = $this->getDoctrine()->getManager('gefra');
                $envio_repo = $em->getRepository(Envio::class);
                $juncao_repo = $em->getRepository(Juncao::class);
                set_time_limit(0); // Avoiding Maximum Execution Timeout
                $batchSize = max((int) $this->container->getParameter('app.bulk.batchsize'), 50);
                $j = 0; // counter

                /*foreach ($worksheet_table as $k => $entry) {
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

                    if (!$envio = $envio_repo->findOneBy(['codigo' => $entry['CODIGO'],
                        'banco' => $banco->getCodigo()])) {
                        // Nova Agência
                        $envio = new Envio();
                        $envio->setCodigo($entry['CODIGO'])
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
                                $envio->{'set' . ucfirst(strtolower($field))}($entry[$field]);
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
                    $envio->setUf($uf->getSigla());

                    if (isset($entry['ATIVO'])) {
                        $envio->setIsActive($entry['ATIVO']);
                    }

                    $em->persist($envio);
                    if ($j++ > $batchSize)
                    {
                        $em->flush();
                        $j = 0;
                    }
                    echo "\n"; // Avoiding Browser Timeout
                }*/
                $em->flush();

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('gefra_envio_index');
            } catch(Exception $e) {
                $error = new FormError('Arquivo inválido! ' . $e->getMessage());
            }
        }

        return $this->render('gefra/envio/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/{id}/editar", name="gefra_envio_edit", methods="GET|POST")
     */
    public function edit(Request $request, Envio $envio): Response
    {
        $form = $this->createForm(EnvioType::class, $envio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('gefra')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('gefra_envio_index', ['id' => $envio->getId()]);
        }

        return $this->render('gefra/envio/edit.html.twig', [
            'juncao' => $envio,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_envio_delete", methods="DELETE")
     */
    public function deleteEnvio(Request $request, Envio $envio): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$envio->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('gefra_envio_index');
        }

        $em = $this->getDoctrine()->getManager('gefra');
        $em->remove($envio);
        $em->flush();

        return $this->redirectToRoute('gefra_juncao_list');
    }



    /**
     * @Route("/{id}", name="get-juncao", methods="GET")
     */
    public function show(Envio $envio): Response
    {
        return $this->render('gefra_juncao_show.html.twig', ['juncao' => $envio]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="gefra-envio-list-json", methods="GET|POST")
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
            ->getRepository(Envio::class);
        $qb = $cidade_repo->createQueryBuilder('a')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);


        if ($search_value != null) {
            $search_value = preg_replace("#[\W]+#", "_", $search_value);
            $qb->orWhere(
                $qb->expr()->like('LOWER(a.codigo)', '?1'),
                $qb->expr()->like('LOWER(a.nome)', '?1'),
                $qb->expr()->like('LOWER(a.cidade)', '?1')
            )->setParameters([1 => '%' . strtolower($search_value) . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');

        $data = [];
        $banco_repo = $this->getDoctrine()->getManager('agencia')->getRepository(Banco::class);
        /* @var $envio Envio */
        foreach ($paginator as $envio) {
            $banco = $banco_repo->findOneByCodigo($envio->getBanco()); // Lazy Load
            $d['juncao'] = unserialize($envio->serialize());
            $d['juncao']['banco'] = unserialize($banco->serialize());
            $d['buttons'] = 'BUTTONS';
            $d['deleteToken'] = $tokenProvider->getToken('delete' . $envio->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $envio->getId())->getValue();
            $d['changetitle'] = $this->get('translator')
                ->trans('gefra.juncao.changestatus.title', ['%name%' => $envio->getNome()]);
            $d['deltitle'] = $this->get('translator')
                ->trans('gefra.juncao.delete.title', ['%name%' => $envio->getNome()]);
            $d['editUrl'] = $this->generateUrl('gefra-envio-edit', ['id' => $envio->getId()]);
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