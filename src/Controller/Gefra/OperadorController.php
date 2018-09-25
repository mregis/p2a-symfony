<?php

namespace App\Controller\Gefra;

use App\Entity\Gefra\Operador;
use App\Entity\Localidade\UF;
use App\Form\Gefra\OperadorType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Gefra\OperadorRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/gefra/operador")
 */
class OperadorController extends Controller
{
    /**
     * @Route("/", name="gefra_operador_index", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('gefra/operador/index.html.twig');
    }

    /**
     * @Route("/novo", name="gefra_operador_new", methods="GET|POST")
     */
    public function _new(Request $request): Response
    {
        $operador = new Operador();
        $form = $this->createForm(OperadorType::class, $operador);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('gefra');
            $operador->setCnpj(preg_replace('#\D#', '', $operador->getCnpj()));
            $em->persist($operador);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('gefra_operador_index');
        }

        return $this->render('gefra/operador/new.html.twig', [
            'operador' => $operador,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/editar", name="gefra_operador_edit", methods="GET|POST")
     */
    public function edit(Request $request, Operador $operador): Response
    {
        $form = $this->createForm(OperadorType::class, $operador);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gefra/operador/index.html.twig', ['id' => $operador->getId()]);
        }

        return $this->render('gefra/operador/edit.html.twig', [
            'operador' => $operador,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_operador_delete", methods="DELETE")
     */
    public function delete(Request $request, Operador $operador): Response
    {
        if ($this->isCsrfTokenValid('delete'.$operador->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($operador);
            $em->flush();
        }

        return $this->redirectToRoute('gefra_operador_index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="gefra_operador_json_list", methods="GET|POST")
     */
    public function getOperadores(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('a.codigo', 'a.nome','a.cnpj',
            'a.endereco', 'a.bairro', 'a.cidade', 'a.uf')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        $cidade_repo = $this->getDoctrine()
            ->getManager('gefra')
            ->getRepository(Operador::class);
        $qb = $cidade_repo->createQueryBuilder('a')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);


        if ($search_value != null) {
            $search_value = preg_replace("#[\W]+#", "_", $search_value);
            $qb->orWhere(
                $qb->expr()->like('LOWER(a.codigo)', '?1'),
                $qb->expr()->like('LOWER(a.nome)', '?1'),
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
        /* @var $operador Operador */
        foreach ($paginator as $operador) {
            $d['operador'] = unserialize($operador->serialize());
            $d['buttons'] = 'BUTTONS';
//            $d['deleteToken'] = $tokenProvider->getToken('delete' . $operador->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $operador->getId())->getValue();
            $d['changetitle'] = $this->get('translator')
                ->trans('gefra.operador.changestatus.title', ['%name%' => $operador->getNome()]);
            $d['deltitle'] = $this->get('translator')
                ->trans('gefra.operador.delete.title', ['%name%' => $operador->getNome()]);
            $d['editUrl'] = $this->generateUrl('gefra_operador_edit', ['id' => $operador->getId()]);
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
     * @Route("/{id}", name="gefra_operador_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Operador $operador): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('gefra.operador.changestatus.title',
            ['%name%' => $operador->getNome()]);
        if ($this->isCsrfTokenValid('put'.$operador->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->
            $operador->setIsActive(!$operador->getIsActive());
            $em->persist($operador);
            $em->flush();
            $message = $this->get('translator')->trans('agencia.change-status.success', ['%name%'=> $agencia->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/cadastro-lote", name="gefra_operador_loadfile", methods="GET|POST")
     */
    public function newOperadorBulk(Request $request): Response
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
                $operador_repo = $em->getRepository(Operador::class);

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

                    if (!$operador = $operador_repo->findOneBy(['codigo' => $entry['CODIGO']])) {
                        // Novo Operador
                        $operador = new Operador();
                        $operador->setCodigo($entry['CODIGO']);

                    } // else
                    // Operador já existente, informações serão atualizadas

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
                                $operador->{'set' . ucfirst(strtolower($field))}($entry[$field]);
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
                    $operador->setBairro($entry['BAIRRO']);
                    $operador->setCep(preg_replace('#\D#', "", $entry['CEP']));
                    $operador->setUf($uf->getSigla());

                    if (isset($entry['ATIVO'])) {
                        $operador->setIsActive($entry['ATIVO']);
                    }

                    $em->persist($operador);
                    if ($j++ > $batchSize)
                    {
                        $em->flush();
                        $j = 0;
                    }
                    echo "\n"; // Avoiding Browser Timeout
                }
                $em->flush();

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('gefra_operador_index');
            } catch(Exception $e) {
                $error = new FormError('Arquivo inválido! ' . $e->getMessage());
            }
        }

        return $this->render('gefra/operador/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/{id}", name="gefra_operador_show", methods="GET")
     */
    public function show(Operador $operador): Response
    {
        return $this->render('gefra_operador/show.html.twig', ['operador' => $operador]);
    }
}
