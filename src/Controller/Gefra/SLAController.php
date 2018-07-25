<?php

namespace App\Controller\Gefra;

use App\Entity\Gefra\Juncao;
use App\Entity\Gefra\Operador;
use App\Entity\Gefra\SLA;
use App\Form\Gefra\SLAType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Gefra\SLARepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/gefra/sla")
 */
class SLAController extends Controller
{
    /**
     * @Route("/", name="gefra_sla_index", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('gefra/sla/index.html.twig');
    }

    /**
     * @Route("/novo", name="gefra_sla_new", methods="GET|POST")
     */
    public function _new(Request $request): Response
    {
        $sLA = new SLA();
        $form = $this->createForm(SLAType::class, $sLA);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('gefra');
            $em->persist($sLA);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('gefra_sla_index');
        }

        return $this->render('gefra/sla/new.html.twig', [
            'sla' => $sLA,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_sla_show", methods="GET")
     */
    public function show(SLA $sLA): Response
    {
        return $this->render('gefra_sla/show.html.twig', ['sla' => $sLA]);
    }

    /**
     * @Route("/{id}/editar", name="gefra_sla_edit", methods="GET|POST")
     */
    public function edit(Request $request, SLA $sLA): Response
    {
        $form = $this->createForm(SLAType::class, $sLA);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gefra_sla_edit', ['id' => $sLA->getId()]);
        }

        return $this->render('gefra_sla/edit.html.twig', [
            'sla' => $sLA,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gefra_sla_delete", methods="DELETE")
     */
    public function delete(Request $request, SLA $sLA): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sLA->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($sLA);
            $em->flush();
        }

        return $this->redirectToRoute('gefra_sla_index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/json", name="gefra_sla_json_list", methods="GET|POST")
     */
    public function getSLAs(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('a.operador', 'a.juncao', 'a.prazo')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        /* @var $sla_repo SLARepository */
        $sla_repo = $this->getDoctrine()
            ->getManager('gefra')
            ->getRepository(SLA::class);
        $qb = $sla_repo->createQueryBuilder('a')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);


        if ($search_value != null) {
            $search_value = preg_replace("#[\W]+#", "_", $search_value);
            $qb->orWhere(
                $qb->expr()->like('LOWER(a.juncao)', '?1'),
                $qb->expr()->like('LOWER(a.operador)', '?1')
            )->setParameters([1 => '%' . strtolower($search_value) . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');
        $data = [];
        /* @var $sla SLA */
        foreach ($paginator as $sla) {
            $d['sla'] = unserialize($sla->serialize());
            $d['buttons'] = 'BUTTONS';
            $d['editToken'] = $tokenProvider->getToken('put' . $sla->getId())->getValue();
            $d['editUrl'] = $this->generateUrl('gefra_sla_edit', ['id' => $sla->getId()]);
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
 * @param Request $request
 * @return Response
 * @Route("/cadastro-lote/", name="gefra_sla_load_file", methods="GET|POST")
 */
public function loadSLAFile(Request $request): Response
{
    $error = null;
    $form = $this->createForm(BulkRegistryType::class);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        try {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = $form->get('registry')->getData();
            // Validando o arquivo
            $serializer = new Serializer(array(), [new CsvEncoder()]);
            $data = $serializer->decode(file_get_contents($file->getPathname()), 'csv', ['as_collection'=>true]);
            $em = $this->getDoctrine()->getManager('gefra');
            $operador_repo = $em->getRepository(Operador::class);
            $juncao_repo = $em->getRepository(Juncao::class);
            $sla_repo = $em->getRepository(SLA::class);

            set_time_limit(0); // Avoiding Maximum Execution Timeout
            $batchSize = max((int) $this->container->getParameter('app.bulk.batchsize'), 50);
            $j = 0; // counter

            foreach ($data as $k => $entry) {
                if (!isset($entry['OPERADOR'],$entry['DESTINO'], $entry['PRAZO'])) {
                    throw new \Exception(
                        sprintf("Erro na linha %d. Verifique se os cabeçalhos estão corretos " .
                            "[OPERADOR,DESTINO,PRAZO].", $k + 2)
                    );
                }

                if (!$operador = $operador_repo->findOneBy(['codigo' => $entry['OPERADOR']])) {
                    throw new \Exception(
                        sprintf("Erro na linha %d. [%s] não é um Código de Operador válido.", $k + 2, $entry['UF'])
                    );
                }
                if (!$juncao = $juncao_repo->findOneBy(['codigo' => $entry['DESTINO']])) {
                    throw new \Exception(
                        sprintf("Erro na linha %d. [%s] não é um Código de Destino válido.", $k + 2, $entry['UF'])
                    );
                }

                if (!$sla = $sla_repo->findOneBy(['operador' => $operador, 'juncao' => $juncao])) {
                    // Novo SLA
                    $sla = new SLA();
                    $sla->setOperador($operador)
                        ->setJuncao($juncao);
                } // else
                // SLA já existente, informações serão atualizadas

                // validando campo Prazo
                if ($entry['PRAZO'] != (int)$entry['PRAZO'] || (int)$entry['PRAZO'] < 1) {
                        throw new \Exception(
                            sprintf("Erro na linha %d. O Prazo [%s] é inválido", $k +2, $entry['CEP']
                            )
                        );
                }
                $sla->setPrazo((int)$entry['PRAZO']);

                $em->persist($sla);
                if ($j++ > $batchSize)
                {
                    $em->flush();
                    $j = 0;
                }
                echo "\n"; // Avoiding Browser Timeout
            }
            $em->flush();

            $this->addFlash('success', 'flash.success.new-bulk');
            return $this->redirectToRoute('gefra_sla_index');
        } catch(Exception $e) {
            $error = new FormError('Arquivo inválido! ' . $e->getMessage());
        }
    }

    return $this->render('gefra/sla/new-bulk.html.twig', [
        'form' => $form->createView(),
        'error' => $error
    ]);

}
}
