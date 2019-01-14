<?php

namespace App\Controller\Gefra;

use App\Entity\Gefra\Transportadora;
use App\Entity\Localidade\UF;
use App\Form\Gefra\TransportadoraType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Gefra\TransportadoraRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/gefra/transportadora")
 */
class TransportadoraController extends AbstractController
{
    /**
     * @Route("/", name="gefra_transportadora_index", methods="GET")
     */
    public function index(TransportadoraRepository $transportadoraRepository): Response
    {
        return $this->render('gefra/transportadora/index.html.twig', ['transportadoras' => $transportadoraRepository->findAll()]);
    }

    /**
     * @Route("/nova", name="gefra_transportadora_new", methods="GET|POST")
     */
    public function newTranportadora(Request $request): Response
    {
        $transportadora = new Transportadora();
        $form = $this->createForm(TransportadoraType::class, $transportadora);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('gefra');
            $transportadora->setCnpj(preg_replace('#\D#', '', $transportadora->getCnpj()));
            $em->persist($transportadora);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('gefra_transportadora_index');
        }

        return $this->render('gefra/transportadora/new.html.twig', [
            'transportadora' => $transportadora,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/editar", name="gefra_transportadora_edit", methods="GET|POST")
     */
    public function edit(Request $request, Transportadora $transportadora): Response
    {
        $form = $this->createForm(TransportadoraType::class, $transportadora);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transportadora->setCnpj(preg_replace('#\D#', '', $transportadora->getCnpj()));
            $this->getDoctrine()->getManager('gefra')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('gefra_transportadora_index');
        }

        return $this->render('gefra/transportadora/edit.html.twig', [
            'transportadora' => $transportadora,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_transportadora_delete", methods="DELETE")
     */
    public function delete(Request $request, Transportadora $transportadora): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transportadora->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transportadora);
            $em->flush();
        }

        return $this->redirectToRoute('gefra_transportadora_index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="gefra_transportadora_json_list", methods="GET|POST")
     */
    public function getTransportadoraes(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('a.codigo', 'a.nome','a.razao_social', 'a.cnpj',
            'a.endereco', 'a.bairro', 'a.cidade', 'a.uf')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        $cidade_repo = $this->getDoctrine()
            ->getManager('gefra')
            ->getRepository(Transportadora::class);
        $qb = $cidade_repo->createQueryBuilder('a')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);


        if ($search_value != null) {
            $search_value = preg_replace("#[\W]+#", "_", $search_value);
            $qb->orWhere(
                $qb->expr()->like('LOWER(a.codigo)', '?1'),
                $qb->expr()->like('LOWER(a.nome)', '?1'),
                $qb->expr()->like('LOWER(a.razao_social)', '?1'),
                $qb->expr()->like('LOWER(a.endereco)', '?1'),
                $qb->expr()->like('LOWER(a.bairro)', '?1'),
                $qb->expr()->like('LOWER(a.cep)', '?1'),
                $qb->expr()->like('LOWER(a.cnpj)', '?1'),
                $qb->expr()->like('LOWER(a.cidade)', '?1')
            )->setParameters([1 => '%' . strtolower($search_value) . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');
        $data = [];
        /* @var $transportadora Transportadora */
        foreach ($paginator as $transportadora) {
            $d['transportadora'] = unserialize($transportadora->serialize());
            $d['buttons'] = 'BUTTONS';
            $d['editToken'] = $tokenProvider->getToken('put' . $transportadora->getId())->getValue();
            $d['changetitle'] = $this->get('translator')
                ->trans('gefra.transportadora.changestatus.title', ['%name%' => $transportadora->getNome()]);
            $d['editUrl'] = $this->generateUrl('gefra_transportadora_edit', ['id' => $transportadora->getId()]);
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
     * @Route("/{id}", name="gefra_transportadora_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Transportadora $transportadora): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('gefra.transportadora.changestatus.title',
            ['%name%' => $transportadora->getNome()]);
        if ($this->isCsrfTokenValid('put'.$transportadora->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->
            $transportadora->setIsActive(!$transportadora->getIsActive());
            $em->persist($transportadora);
            $em->flush();
            $message = $this->get('translator')->trans('agencia.change-status.success', ['%name%'=> $agencia->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/cadastro-lote", name="gefra_transportadora_loadfile", methods="GET|POST")
     */
    public function newTransportadoraBulk(Request $request): Response
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
                $serializer = new Serializer(array(), [new CsvEncoder()]);

                $data = $serializer->decode(file_get_contents($file->getPathname()), 'csv', ['as_collection'=>true]);
                $em = $this->getDoctrine()->getManager('gefra');
                $uf_repo = $this->getDoctrine()->getManager('locais')->getRepository(UF::class);
                $transportadora_repo = $em->getRepository(Transportadora::class);

                set_time_limit(0); // Avoiding Maximum Execution Timeout
                $batchSize = max((int) $this->container->getParameter('app.bulk.batchsize'), 50);
                $j = 0; // counter

                foreach ($data as $k => $entry) {
                    if (!isset($entry['CODIGO'],$entry['NOME'], $entry['CNPJ'],
                        $entry['ENDERECO'], $entry['BAIRRO'],
                        $entry['CIDADE'], $entry['UF'], $entry['ATIVO'])) {
                        throw new \Exception(
                            sprintf("Erro na linha %d. Verifique se os cabeçalhos estão corretos " .
                                "[CODIGO,NOME,CNPJ,ENDERECO,BAIRRO,CIDADE,UF,ATIVO].", $k + 2)
                        );
                    }

                    if (!$uf = $uf_repo->findBySigla($entry['UF'])) {
                        throw new \Exception(
                            sprintf("Erro na linha %d. [%s] não é uma UF válida.", $k + 2, $entry['UF'])
                        );
                    }

                    if (!$transportadora = $transportadora_repo->findOneBy(['codigo' => $entry['CODIGO']])) {
                        // Novo Transportadora
                        $transportadora = new Transportadora();
                        $transportadora->setCodigo($entry['CODIGO']);

                    } // else
                    // Transportadora já existente, informações serão atualizadas

                    // Detecting encoding and converting to UTF-8 before persist into database
                    try {
                        // campos texto
                        foreach (['NOME','ENDERECO','BAIRRO','CIDADE'] as $field) {
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
                        foreach (['CODIGO','NOME','CNPJ','ENDERECO','CIDADE','UF'] as $field) {
                            if (isset($entry[$field]) && $entry[$field] != '') {
                                $transportadora->{'set' . ucfirst(strtolower($field))}($entry[$field]);
                            } else {
                                throw new \Exception(
                                    sprintf("Erro na linha %d. [%s] não pode ficar em branco.",
                                        $k + 2, $field)
                                );
                            }
                        }

                        if (!preg_match("#\d{5}-?\d{3}#", $entry['CEP'])) {
                            throw new \Exception(sprintf("O CEP [%s] é inválido", $entry['CEP']));
                        }

                    } catch (Exception $e) {
                        throw new \Exception(sprintf('Error on line %d, field %s. more -> ' .
                            $e->getMessage(), $k +2, $field));
                    }
                    // demais campos
                    $transportadora->setBairro($entry['BAIRRO']);
                    $transportadora->setCep(preg_replace('#\D#', "", $entry['CEP']));
                    $transportadora->setUf($uf->getSigla());

                    if (isset($entry['ATIVO'])) {
                        $transportadora->setIsActive($entry['ATIVO']);
                    }

                    $em->persist($transportadora);
                    if ($j++ > $batchSize)
                    {
                        $em->flush();
                        $j = 0;
                    }
                    echo "\n"; // Avoiding Browser Timeout
                }
                $em->flush();

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('gefra_transportadora_index');
            } catch(Exception $e) {
                $error = new FormError('Arquivo inválido! ' . $e->getMessage());
            }
        }

        return $this->render('gefra/transportadora/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/{id}", name="gefra_transportadora_show", methods="GET")
     */
    public function show(Transportadora $transportadora): Response
    {
        return $this->render('gefra_transportadora/show.html.twig', ['transportadora' => $transportadora]);
    }
}
