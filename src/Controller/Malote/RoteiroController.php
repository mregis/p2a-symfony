<?php

namespace App\Controller\Malote;

use App\Entity\Gefra\EnvioFile;
use App\Entity\Main\UploadDataFile;
use App\Entity\Malote\Roteiro;
use App\Form\Malote\RoteiroType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Malote\RoteiroRepository;
use App\Util\StringUtils;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/malote/roteiro")
 */
class RoteiroController extends AbstractController
{
    /**
     * @Route("/", name="malote_roteiro_index", methods="GET")
     */
    public function index(RoteiroRepository $roteiroRepository): Response
    {
        return $this->render('malote/roteiro/index.html.twig');
    }

    /**
     * @Route("/novo", name="malote_roteiro_new", methods="GET|POST")
     */
    public function newRoteiro(Request $request): Response
    {
        $roteiro = new Roteiro();
        $form = $this->createForm(RoteiroType::class, $roteiro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager('malote');
            $em->persist($roteiro);
            $em->flush();
            $this->addFlash('success', 'flash.success.new');
            return $this->redirectToRoute('malote_roteiro_index');
        }

        return $this->render('malote/roteiro/new.html.twig', [
            'roteiro' => $roteiro,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_roteiro_show", methods="GET")
     */
    public function show(Roteiro $roteiro): Response
    {
        return $this->render('malote/roteiro/show.html.twig', ['roteiro' => $roteiro]);
    }

    /**
     * @Route("/{id}/editar", name="malote_roteiro_edit", methods="GET|POST")
     */
    public function edit(Request $request, Roteiro $roteiro): Response
    {
        $form = $this->createForm(RoteiroType::class, $roteiro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager('malote')->flush();
            $this->addFlash('success', 'flash.success.edit');
            return $this->redirectToRoute('malote_roteiro_edit', ['id' => $roteiro->getId()]);
        }

        return $this->render('malote/roteiro/edit.html.twig', [
            'roteiro' => $roteiro,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="malote_roteiro_delete", methods="DELETE")
     */
    public function delete(Request $request, Roteiro $roteiro): Response
    {
        if ($this->isCsrfTokenValid('delete'.$roteiro->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('malote');
            $em->remove($roteiro);
            $em->flush();
        }

        return $this->redirectToRoute('malote_roteiro_index');
    }

    /**
     * @Route("/{id}", name="malote_roteiro_changestatus", methods="PUT")
     */
    public function changeStatus(Request $request, Roteiro $roteiro): Response
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $roteiro_nome = $roteiro->getAgencia() . ' x ' . $roteiro->getUnidade();
        $title = $this->get('translator')->trans('malote.roteiro.change-status.title', ['%name%' => $roteiro_nome]);
        if ($this->isCsrfTokenValid('put'.$roteiro->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager('malote');
            $roteiro->setAtivo(!$roteiro->getAtivo());
            $em->persist($roteiro);
            $em->flush();
            $message = $this->get('translator')->trans('malote.malha.change-status.success', ['%name%'=> $roteiro_nome]);
            $statusMode = 'success';
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/roteiro/json", name="malote_roteiro_json", methods="GET|POST")
     */
    public function getRoteiros(Request $request): JsonResponse
    {
        // Query Parameters
        $length = $request->get('length', 10);
        $start =  $request->get('start', 0);
        $draw =  $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0=>['column' => 0]])[0]['column'];
        // somente uma coluna para ordena��o aqui
        $orderColumn = array('r.agencia','r.rota', 'r.transportadora', 't.unidade', 't.frequencia',
            'm.nome', 't.criado_em', 't.lote')[$orderNumColumn];
        $sortType = $request->get('order',[0=>['dir' => 'ASC']])[0]['dir'];
        /* @var $roteiro_repo RoteiroRepository */ 
        $roteiro_repo = $this->getDoctrine()
            ->getManager('malote')
            ->getRepository(Roteiro::class);
        $qb = $roteiro_repo->createQueryBuilder('r')
            ->select('r, m')
            ->innerJoin('r.malha', 'm')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy($orderColumn, $sortType);

        if ($search_value != null) {
            $search_value_canonical = StringUtils::slugify($search_value);
            $qb->orWhere(
                $qb->expr()->eq('r.nome', '?1'),
                $qb->expr()->like('r.rota)', '?1'),
                $qb->expr()->like('r.transportadora)', '?1'),
                $qb->expr()->like('r.unidade)', '?1'),
                $qb->expr()->like('r.frequencia)', '?1'),
                $qb->expr()->like('m.nome_canonico)', '?2')
            )->setParameters([1 => $search_value, 2 => $search_value_canonical]);
        }
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

        /* @var $tokenProvider TokenProviderInterface */
        $tokenProvider = $this->container->get('security.csrf.token_manager');

        $data = [];
        /* @var $roteiro Roteiro */
        foreach ($paginator as $roteiro) {
            $malha = $roteiro->getMalha(); // Lazy Load
            $roteiro_nome = $roteiro->__toString();
            $d['roteiro'] = unserialize($roteiro->serialize());
            $d['roteiro']['nome'] = $roteiro_nome;
            $d['buttons'] = 'BUTTONS';
            $d['deleteToken'] = $tokenProvider->getToken('delete' . $roteiro->getId())->getValue();
            $d['editToken'] = $tokenProvider->getToken('put' . $roteiro->getId())->getValue();
            $d['changetitle'] = $this->get('translator')
                ->trans('malote.roteiro.change-status.title', ['%name%' => $roteiro_nome]);
            $d['deltitle'] = $this->get('translator')
                ->trans('malote.roteiro.delete.title', ['%name%' => $roteiro_nome]);
            $d['editUrl'] = $this->generateUrl('malote_roteiro_edit', ['id' => $roteiro->getId()]);
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
     * @Route("/carregar-planilha-roteiros/", name="malote_roteiro_load_xlsfile", methods="GET|POST")
     */
    public function loadXLSXRoteiroFile(Request $request): Response
    {
        $form = $this->createForm(BulkRegistryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = $form->get('registry')->getData();

            $file_id = hash_file('crc32b', $file->getPathname());

            $em = $this->getDoctrine()->getManager();
            if (!$upload_file = $em->getRepository(UploadDataFile::class)->findOneBy(['hashid' => $file_id])) {
                if ($dest_dir = $this->container->getParameter('uploaddatafiles.directory')) {
                    $file = $file->move($dest_dir, $file_id . '.' . $file->getClientOriginalExtension());
                }

                $upload_file = new UploadDataFile();
                $upload_file->setPath($file->getPathname())
                    ->setHashId($file_id)
                    ->setType('ROTEIRO_DATA_FILE')
                    ->setStatus(EnvioFile::NEW_SEND)
                    ->setUploadedBy($this->getUser()->getId())
                    ->setDescription('Arquivo de Roteiro')
                ;
                $em->persist($upload_file);
                $em->flush();
                $this->addFlash('success', 'flash.success.uploaded');
            } else {
                // Arquivo j� foi enviado anteriormente exatamente como est�. N�o faz sentido continuar
                $error = new FormError('flash.error.already-uploaded');
                $form->addError($error);
                $form->get('registry')->addError(
                    new FormError($this->get('translator')->trans('flash.error.already-uploaded'))
                );
            }
        }
        return $this->render('malote/roteiro/new-bulk.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
