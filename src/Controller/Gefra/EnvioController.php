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
use App\Entity\Gefra\Juncao;
use App\Entity\Gefra\Ocorrencia;
use App\Entity\Gefra\Operador;
use App\Entity\Localidade\UF;
use App\Form\Gefra\EnvioType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Gefra\JuncaoRepository;
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
     * @Route("/carregar-envios", name="gefra_envio_load_xmlfile", methods="GET|POST")
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
                $domDocument = new \DOMDocument();
                $domDocument->load($file->getPathname());

                $worksheet_tableRows = $domDocument->getElementsByTagName('Row');
                $entries = array();
                $heads = ['documento','volume','valor','peso','fornecedor','localização','solicitação'];
                $found = false;
                $i = 1;
                foreach( $worksheet_tableRows as $idx => $rowChild) {
                    /* @var $rowChild \DomNode */
                    if ($rowChild->childNodes->length > 10 ) {
                        if ($found) {
                            // Já foi encontrado a linha dos cabeçalhos, essa é uma de dados
                            foreach($entries[0] as $ix => $nodeName) {
                                $entries[$i][$nodeName] = $rowChild->childNodes->item($ix)->nodeValue;
                            }
                            $i++;
                        } else {
                            // linha que queremos
                            foreach ($rowChild->childNodes as $ix => $cellNode) { // Procurando os elementos corretos
                                $ns = $cellNode;
                                if (strtolower(trim($ns->nodeName)) == 'cell' &&
                                    in_array(strtolower(trim($ns->nodeValue)), $heads)
                                ) {
                                    // criando o vetor com os dados a serem populados
                                    $entries[0][$ix] = strtolower(trim($cellNode->nodeValue));
                                    $found = true;
                                }
                            }
                        }
                    }
                }
                if ($found == false) {
                    throw new \Exception("O arquivo parece não conter " .
                        "informações de envios. Verifique o conteúdo.");
                }

                $em = $this->getDoctrine()->getManager('gefra');
                $envio_repo = $em->getRepository(Envio::class);
                /* @var $juncao_repo JuncaoRepository */
                $juncao_repo = $em->getRepository(Juncao::class);
                $operador_repo = $em->getRepository(Operador::class);
                set_time_limit(0); // Avoiding Maximum Execution Timeout
                $batchSize = max((int) $this->container->getParameter('app.bulk.batchsize'), 50);
                $j = 0; // counter
                $lote = date('YmdH');
                for($i = 1; $i < count($entries); $i++) {
                    // Verificando se existe a junção requisitada
                    if (!$juncao = $juncao_repo->findOneBy(['codigo' => $entries[$i]['localização']])) {
                        continue;
                        /*
                        throw new \Exception(
                            sprintf("A Junção para o código [%s] não foi encontrado. " .
                                "Se a informação está correta é necessário criar o cadastro primeiro.",
                                $entries[$i]['localização'])
                        );
                        */
                    }

                    // Validando o operador
                    if (!$operador = $operador_repo->findOneByCodigo($entries[$i]['fornecedor'])) {
                        throw new \Exception(
                            sprintf("Operador [%s] não foi encontrado. " .
                                "Se a informação está correta é necessário criar o cadastro primeiro.",
                                $entries[$i]['fornecedor'])
                        );
                    }

                    // Verificando se já não existe em envio para a GRM (documento) passado
                    if (!$envio = $envio_repo->findOneBy(['solicitacao' => $entries[$i]['solicitação']])) {
                        // Nova Agência
                        $envio = new Envio();
                        $envio->setSolicitacao($entries[$i]['solicitação']);
                    } else {
                        // existe
                        $b = $envio->getSolicitacao() == $entries[$i]['solicitação'];
                    }

                    // campos obrigatórios
                    foreach (['valor','peso', 'volume', 'documento'] as $field) {
                        if (isset($entries[$i][$field]) && $entries[$i][$field] != '') {
                            $envio->{'set' . ucfirst(strtolower($field))}($entries[$i][$field]);
                        } else {
                            throw new \Exception(
                                sprintf("Erro de dados no envio %s. [%s] não pode ficar em branco.",
                                    $entries[$i]['documento'], $field)
                            );
                        }
                    }

                    $envio->setLote($lote)
                        ->setJuncao($juncao)
                        ->setOperador($operador)
                        ;
                    $em->persist($envio);
                    if ($j++ > $batchSize) {
                        $em->flush();
                        $j = 0;
                    }
                    echo "\n"; // Avoiding Browser Timeout
                }

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
            'envio' => $envio,
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

        return $this->redirectToRoute('gefra_envio_index');
    }



    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="gefra_envio_list_json", methods="GET|POST")
     */
    public function getEnvios(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('e.dt_emissao_cte', 'e.cte','o.nome', 'j.nome', 'j.cidade', 'j.uf')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'DESC']])[0]['dir'];
        $cidade_repo = $this->getDoctrine()
            ->getManager('gefra')
            ->getRepository(Envio::class);
        $qb = $cidade_repo->createQueryBuilder('e')
            ->select('e, j, o, count(eo) as qt_ocorrencias')
            ->innerJoin('e.juncao', 'j')
            ->innerJoin('e.operador', 'o')
            ->leftJoin('e.ocorrencias', 'eo')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->groupBy('e,j,o')
            ->orderBy($orderColumn, $sortType);

        if ($search_value != null) {
            $search_value = preg_replace("#[\W]+#", "_", $search_value);
            $qb->orWhere(
                $qb->expr()->like('LOWER(e.cte)', '?1'),
                $qb->expr()->like('LOWER(e.solicitacao)', '?1'),
                $qb->expr()->like('LOWER(e.grm)', '?1'),
                $qb->expr()->like('LOWER(j.nome)', '?1'),
                $qb->expr()->like('LOWER(j.cidade)', '?1')
            )->setParameters([1 => '%' . strtolower($search_value) . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');

        $repo_ocorr = $this->getDoctrine()->getManager('gefra')->getRepository(Ocorrencia::class);
        $data = [];
        /* @var $envio Envio */
        foreach ($paginator as $result) {
            $envio = $result[0];
            $d['envio'] = unserialize($envio->serialize());
            $d['envio']['qt_ocorrencias'] = $result['qt_ocorrencias'];
            // status do Envio
            if ($result['qt_ocorrencias'] > 0) {
                /* @var $last_ocorrencia Ocorrencia */
                $last_ocorrencia = $repo_ocorr->findLastByEnvio($envio);
                $d['envio']['status'] = $last_ocorrencia->getType();
            } else {
                $d['envio']['status'] = 'NOVO';
            }

            // Status da Entrega
            $d['envio']['status_entrega'] = '';
            if ($envio->getDtEntrega() != null ) { // Envio já foi entregue
                if ($envio->getDtPrevisaoEntrega() == null) { // Data Previsão está vazia (Erro)
                    $envio->setDtPrevisaoEntrega($envio->getDtEntrega()); // Não pode haver data de Previsao vazia
                }
                if ($envio->getDtEntrega() >= $envio->getDtPrevisaoEntrega()) {
                    $d['envio']['status_entrega'] = 'ENTREGUE NO PRAZO';
                } else {
                    $d['envio']['status_entrega'] = 'ENTREGUE COM ATRASO';
                }
            } else { // item ainda não foi marcado como entregue
                if ($envio->getDtPrevisaoEntrega() == null) { // Data Previsão está vazia
                    $d['envio']['status_entrega'] = 'INDEFINIDO';
                } else { // Já possui data de Previsão
                    if ($envio->getDtPrevisaoEntrega() > new \DateTime()) {
                        $d['envio']['status_entrega'] = 'ENVIO DENTRO DO PRAZO - AGENDADO';
                    } else if ($envio->getDtPrevisaoEntrega() == new \DateTime()) {
                        $d['envio']['status_entrega'] = 'ENVIO DENTRO DO PRAZO - PARA HOJE';
                    } else {
                        $d['envio']['status_entrega'] = 'AGUARDANDO INFORME - POSSÍVEL ATRASO';
                    }
                }
            }

            $d['buttons'] = 'BUTTONS';
            $d['deleteToken'] = $tokenProvider->getToken('delete' . $envio->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $envio->getId())->getValue();
            $d['deltitle'] = $this->get('translator')
                ->trans('gefra.juncao.delete.title', ['%name%' => $envio->getCte()]);
            $d['editUrl'] = $this->generateUrl('gefra_envio_edit', ['id' => $envio->getId()]);
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
     * @Route("/{id}", name="get-juncao", methods="GET")
     */
    public function show(Envio $envio): Response
    {
        return $this->render('gefra_evio_show.html.twig', ['juncao' => $envio]);
    }

}