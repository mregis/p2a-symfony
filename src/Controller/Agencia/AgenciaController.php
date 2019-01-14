<?php

namespace App\Controller\Agencia;

use App\Entity\Agencia\Agencia;
use App\Entity\Agencia\Banco;
use App\Entity\Localidade\UF;
use App\Form\Agencia\AgenciaType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Agencia\AgenciaRepository;
use App\Util\CalculationsBancoBradesco;
use App\Util\CalculationsBancoFactory;
use App\Util\CalculationsBancoInterface;
use App\Util\StringUtils;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/agencias")
 */
class AgenciaController extends AbstractController
{
    /**
     * @Route("/", name="agencias_home", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('agencias/index.html.twig');
    }

    /**
     * @Route("/agencia", name="agencias_agencia_index", methods="GET")
     */
    public function listAgencias(): Response
    {
        return $this->render('agencias/agencia/index.html.twig', ['agencias' => []]);
    }

    /**
     * @Route("/agencia/nova", name="agencias_agencia_new", methods="GET|POST")
     */
    public function newAgencia(Request $request, TranslatorInterface $trans): Response
    {
        $agencium = new Agencia();
        $form = $this->createForm(AgenciaType::class, $agencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verificando Dígito Verificador

            /* @var $formData Agencia */
            $formData =  $form->getData();
            /* @var $calculations CalculationsBancoInterface */
            $calculations = CalculationsBancoFactory::get($formData->getBanco()->getCodigo());
            $dv = $calculations->calculateDvAgencia($formData->getCodigo());
            if ($dv == $formData->getDv()) {
                $em = $this->getDoctrine()->getManager('agencia');
                $em->persist($agencium);
                $em->flush();
                $this->addFlash('success', 'flash.success.new');
                return $this->redirectToRoute('agencias_agencia_index');
            } else {
                $form['dv']->addError(new FormError(
                    $trans->trans('agencia.invalid-dv')
                ));
            }
        }

        return $this->render('agencias/agencia/new.html.twig', [
            'agencium' => $agencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/agencia/cadastro-lote", name="agencias_agencia_loadfile", methods="GET|POST")
     */
    public function newAgenciaBulk(Request $request): Response
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
                $em = $this->getDoctrine()->getManager('agencia');
                $uf_repo = $this->getDoctrine()->getManager('locais')->getRepository(UF::class);
                $agencia_repo = $em->getRepository(Agencia::class);
                $banco_repo = $em->getRepository(Banco::class);
                set_time_limit(0); // Avoiding Maximum Execution Timeout
                $batchSize = max((int) $this->container->getParameter('app.bulk.batchsize'), 50);
                $j = 0; // counter

                    foreach ($data as $k => $entry) {
                        if (!isset($entry['BANCO'],$entry['NOME'], $entry['CODIGO'],
                            $entry['CEP'], $entry['LOGRADOURO'], $entry['BAIRRO'],
                            $entry['CIDADE'], $entry['UF'], $entry['NUMERAL'])) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. Verifique se os cabeçalhos estão corretos " .
                                    "[BANCO,NOME,CODIGO,CODIGO,CEP,LOGRADOURO,NUMERAL,BAIRRO,CIDADE,UF[,ATIVO].", $k + 2)
                            );
                        }
                        // Validando o codigo da Agencia informado
                        if ($entry['CODIGO'] !== (string)(int)$entry['CODIGO']) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. [%s] não é código de AGÊNCIA válido.",
                                    $k + 2, $entry['CODIGO'])
                            );
                        }

                        if (!$banco = $banco_repo->findOneByCodigo($entry['BANCO'])) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. [%s] não é código de BANCO válido.", $k + 2, $entry['BANCO'])
                            );
                        }

                        if (!$calculations = CalculationsBancoFactory::get($banco->getCodigo())){
                            throw new \Exception(
                                sprintf("Erro na linha %d. O Banco [%s] não é suportado.", $k + 2, $entry['BANCO'])
                            );
                        }

                        if (!$uf = $uf_repo->findBySigla($entry['UF'])) {
                            throw new \Exception(
                                sprintf("Erro na linha %d. [%s] não é uma UF válida.", $k + 2, $entry['UF'])
                            );
                        }


                        if (!$agencia = $agencia_repo->findOneBy(['codigo' => $entry['CODIGO'],
                            'banco' => $banco])) {
                            // Nova Agência
                            $agencia = new Agencia();
                            $agencia->setCodigo($entry['CODIGO'])
                                ->setBanco($banco)
                                ->setDv($calculations::calculateDvAgencia($entry['CODIGO']));
                            $banco->addAgencia($agencia);
                        } // else
                        // Agência já existente, informações serão atualizadas

                        // Detecting encoding and converting to UTF-8 before persist into database
                        try {
                            // campos texto
                            foreach (['NOME','CIDADE','LOGRADOURO','BAIRRO','COMPLEMENTO'] as $field) {
                                if (isset($entry[$field])) {
                                    $current_encoding = mb_detect_encoding(
                                        $entry[$field], 'UTF-8, ISO-8859-1, ASCII'
                                    );
                                    $data = mb_convert_encoding($entry[$field], 'UTF-8', $current_encoding);
                                    $data = iconv('UTF-8', "UTF-8//TRANSLIT ", $data);
                                    $entry[$field] = trim($data);
                                }
                            }
                            // Validando CEP
                            if (!preg_match('#^(\d{5})-?(\d{3})$#', $entry['CEP'], $matches)) {
                                throw new \Exception(
                                    sprintf("Erro na linha %d. [%s] não é um CEP válido.",
                                        $k + 2, $entry['CEP'])
                                );
                            } else {
                                $agencia->setCep($matches[1].$matches[2]);
                            }

                            // campos obrigatórios
                            foreach (['NOME','CIDADE','LOGRADOURO'] as $field) {
                                if (isset($entry[$field]) && $entry[$field] != '') {
                                    $agencia->{'set' . ucfirst(strtolower($field))}($entry[$field]);
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
                        $agencia->setNumeral(trim($entry['NUMERAL']))
                            ->setUf($uf->getSigla());

                        if (isset($entry['ATIVO'])) {
                            $agencia->setIsActive($entry['ATIVO']);
                        }

                        $em->persist($agencia);
                        if ($j++ > $batchSize)
                        {
                            $em->flush();
//                            $em->clear();
                            $j = 0;
                        }
                        echo "\n"; // Avoiding Browser Timeout
                    }
                    $em->flush();

                $this->addFlash('success', 'flash.success.new-bulk');
                return $this->redirectToRoute('agencias_agencia_index');
            } catch(Exception $e) {
                $error = new FormError('Arquivo inválido! ' . $e->getMessage());
            }
        }

        return $this->render('agencias/agencia/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }

    /**
     * @Route("/agencia/{id}/editar", name="agencias_agencia_edit", methods="GET|POST")
     */
    public function edit(Request $request, Agencia $agencium): Response
    {
        $form = $this->createForm(AgenciaType::class, $agencium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('agencia')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('agencias_agencia_index');
        }

        return $this->render('agencias/agencia/edit.html.twig', [
            'agencium' => $agencium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/agencia/{id}", name="agencias_agencia_delete", methods="DELETE")
     */
    public function deleteAgencia(Request $request, Agencia $agencium): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$agencium->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('agencias_agencia_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($agencium);
        $em->flush();

        return $this->redirectToRoute('agencias_agencia_index');
    }

    /**
     * @Route("/agencia/{id}", name="agencias_agencia_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Agencia $agencia, TranslatorInterface $trans): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $trans->trans('agencia.change-status.title', ['%name%' => $agencia->getNome()]);
        if ($this->isCsrfTokenValid('put'.$agencia->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('agencia');
            $agencia->setIsActive(!$agencia->getIsActive());
            $em->persist($agencia);
            $em->flush();
            $message = $trans->trans('agencia.change-status.success', ['%name%'=> $agencia->getNome()]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

    /**
     * @return Response
     * @Route("/arquivo-modelo/{source}", name="agencias_agencia_samplefile")
     */
    public function downloadSampleCVS(Request $request, $source, TranslatorInterface $trans): Response
    {
        $filename = $this->getParameter('app.samples.dir');
        switch ($source) {
            case 'agencia':
                $filename .= 'agencia.sample.csv';
                $outputname = $trans->trans('agencia.sample-filename');
                break;

            default:
                throw new NotFoundHttpException();
        }
        return $this->file($filename, $outputname . '.csv');
    }

    /**
     * @Route("/agencia/{id}", name="agencias_agencia_show", methods="GET")
     */
    public function show(Agencia $agencium): Response
    {
        return $this->render('agencias_agencia_show.html.twig', ['agencium' => $agencium]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/agencia/json", name="agencias_agencia_json", methods="GET|POST")
     */
    public function getAgencias(Request $request, TranslatorInterface $trans): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('a.banco','a.nome', 'a.codigo', 'a.cep', 'a.logradouro',
            'a.bairro', 'a.cidade', 'a.uf')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        /* @var $agencia_repo AgenciaRepository */
        $agencia_repo = $this->getDoctrine()
            ->getManager('agencia')
            ->getRepository(Agencia::class);
        $qb = $agencia_repo->createQueryBuilder('a')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);


        if ($search_value != null) {
            $search_value = preg_replace('#\D#', '', $search_value);
            $qb->orWhere(
                $qb->expr()->eq('a.codigo', '?1'),
                $qb->expr()->like('LOWER(a.nome)', '?2'),
                $qb->expr()->like('LOWER(a.cidade)', '?2'),
                $qb->expr()->like('LOWER(a.logradouro)', '?2'),
                $qb->expr()->like('LOWER(a.bairro)', '?2')
            )->setParameters([1=> (int)$search_value, 2 => '%' . StringUtils::slugify($search_value) . '%']);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');

        $data = [];
        /* @var $agencia Agencia */
        foreach ($paginator as $agencia) {
            $banco = $agencia->getBanco(); // Lazy Load
            $d['agencia'] = unserialize($agencia->serialize());
            $d['buttons'] = 'BUTTONS';
            $d['deleteToken'] = $tokenProvider->getToken('delete' . $agencia->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $agencia->getId())->getValue();

            $d['changetitle'] = $trans->trans('agencia.change-status.title', ['%name%' => $agencia->getNome()]);
            $d['deltitle'] = $trans->trans('agencia.delete.title', ['%name%' => $agencia->getNome()]);
            $d['editUrl'] = $this->generateUrl('agencias_agencia_edit', ['id' => $agencia->getId()]);
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
