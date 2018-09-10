<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 18/07/2018
 * Time: 17:20
 */

namespace App\Controller\Gefra;

use App\Entity\Gefra\Envio;
use App\Entity\Gefra\EnvioFile;
use App\Entity\Gefra\Juncao;
use App\Entity\Gefra\Ocorrencia;
use App\Entity\Gefra\Operador;
use App\Entity\Gefra\SLA;
use App\Entity\Gefra\Transportadora;
use App\Entity\Main\User;
use App\Form\Gefra\EnvioType;
use App\Form\Type\BulkRegistryType;
use App\Repository\Gefra\EnvioFileRepository;
use App\Repository\Gefra\JuncaoRepository;
use App\Util\StringUtils;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
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
     * @Route("/carregar-planilha-envios", name="gefra_envio_load_xlsfile", methods="GET|POST")
     */
    public function loadXLSXEnvioFile(Request $request): Response
    {
        $form = $this->createForm(BulkRegistryType::class);
        $form->add('transportadora', EntityType::class, array(
            'class' => Transportadora::class,
            'placeholder' => 'choice-field.placeholder',
            'label' => 'fields.name.transportadora',
            'required' => true,
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = $form->get('registry')->getData();

            $file_id = hash_file('crc32b', $file->getPathname());

            $em = $this->getDoctrine()->getManager('gefra');
            if (!$envio_file = $em->getRepository(EnvioFile::class)->findOneBy(['hashid' => $file_id])) {
                if ($dest_dir = $this->container->getParameter('enviofiles.directory')) {
                    $file = $file->move($dest_dir, $file_id . '.' . $file->getClientOriginalExtension());
                }

                $envio_file = new EnvioFile();
                $envio_file->setPath($file->getPathname())
                    ->setHashId($file_id)
                    ->setStatus(EnvioFile::NEW_SEND)
                    ->setUploadedBy($this->getUser()->getId())
                    ->setTransportadora($form->get('transportadora')->getData())
                    ;
                $em->persist($envio_file);
                $em->flush();
                $this->addFlash('success', 'flash.success.uploaded');
            } else {
                // Arquivo já foi enviado anteriormente exatamente como está. Não faz sentido continuar
                $error = new FormError('flash.error.already-uploaded');
                $form->addError($error);
                $form->get('registry')->addError(
                    new FormError($this->get('translator')->trans('flash.error.already-uploaded'))
                );
            }
        }
        return $this->render('gefra/envio/new-bulk.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/carregar-xml-envios", name="gefra_envio_load_xmlfile", methods="GET|POST")
     */
    public function loadXMLFile(Request $request): Response
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
                $heads = ['documento', 'volume', 'valor', 'peso', 'fornecedor', 'localização', 'solicitação'];
                $found = false;
                $i = 1;
                foreach ($worksheet_tableRows as $idx => $rowChild) {
                    /* @var $rowChild \DomNode */
                    if ($rowChild->childNodes->length > 10) {
                        if ($found) {
                            // Já foi encontrado a linha dos cabeçalhos, essa é uma de dados
                            foreach ($entries[0] as $ix => $nodeName) {
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
                $batchSize = max((int)$this->container->getParameter('app.bulk.batchsize'), 50);
                $j = 0; // counter
                $lote = date('YmdH');
                for ($i = 1; $i < count($entries); $i++) {
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
                    foreach (['valor', 'peso', 'volume', 'documento'] as $field) {
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
                        ->setOperador($operador);
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
            } catch (Exception $e) {
                $error = new FormError('Arquivo inválido! ' . $e->getMessage());
            }
        }

        return $this->render('gefra/envio/new-bulk.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);

    }


    /**
     * @param Request $request
     * @return Response
     * @Route("/processar-planilha-envios", name="gefra_envio_proccess_xlsfile", methods="GET|POST")
     */
    public function proccessXLSXFile(Request $request): Response
    {
        $error = null;
        $form = $this->createForm(BulkRegistryType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {

                /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
                $file = $form->get('registry')->getData();
                $reader = new Xlsx();
                $reader->setReadDataOnly(true);
                echo "\n"; // resetting browser timeout
                set_time_limit(0); // Avoiding Maximum Execution Timeout
                $spreadsheet = $reader->load($file->getPathname());
                // Procurando a linha do cabeçalho
                for ($col = 'A'; $col < 'Z'; $col++) { // Procurar da coluna A a Z
                    for ($line = 1; $line < 5; $line++) { // e da linha 1 a 5
                        if (strtoupper($spreadsheet->getActiveSheet()
                                ->getCell($col . $line)) == 'GRM'
                        ) {
                            // célula desejada encontrada
                            $found = true;
                            break 2;
                        }
                    }
                }
                if ($found == false) {
                    throw new \Exception("O arquivo parece não conter " .
                        "informações de envios. Verifique o conteúdo.");
                }

                // @todo Converter para tornar estas informações dinamicas e personalizáveis
                // Linha dos cabeçalhos encontrada, determinando quais colunas devem ser lidas
                $mandatoryColumns = [
                    "GRM" => "grm",
                    "JUNÇÃO" => "juncao",
                    "OP. LOGÍSTCIO" => "operador",
                    "VARREDURA" => "dt_varredura",
                    "VOL" => "qt_vol",
                    "PESO" => "peso",
                    "VALOR" => "valor",
                    "COLETA" => "dt_previsao_coleta"];
                $updatingColumns = ["CTE" => "cte",
                    "EMISSÃO CTE" => "dt_emissao_cte",
                    "DATA ENTREGA" => "dt_entrega",
                    "NOME RECEBEDOR" => "recebedor",
                    "CÓD FUNCIONAL" => "doc_recebedor"];
                $headers = []; // Irá armanezar os Metadados de mapeamento Planilha x Entidade
                // Procurando as informações obrigatórias
                foreach ($mandatoryColumns as $column => $entityAttribute) {
                    $col = 'A';
                    $precol = '';
                    while ($col) { // Procurar da coluna A a AZ (54 colunas)
                        if (strtoupper($spreadsheet->getActiveSheet()
                                ->getCell($precol . $col . $line)) == $column
                        ) {
                            // célula desejada encontrada
                            $headers[$column] = $precol . $col;
                            break; // saltando para a proxima coluna
                        }

                        if ($col != 'Z') {
                            $col++;
                        } else {
                            if ($precol != 'A') {
                                $precol = 'A';
                            } else {
                                throw new Exception(
                                    sprintf('A coluna %s não está presente na lina de cabeçalhos. Processo abortado!',
                                        $column)
                                );
                            }
                            $col = 'A';
                        }

                    }
                }

                // Procurando as informações opcionais
                foreach ($updatingColumns as $column => $entityAttribute) {
                    $col = 'A';
                    $precol = '';
                    while ($col) { // Procurar da coluna A a AZ (54 colunas)
                        if (strtoupper($spreadsheet->getActiveSheet()
                                ->getCell($precol . $col . $line)) == $column
                        ) {
                            // célula desejada encontrada
                            $headers[$column] = $precol . $col;
                            break;
                        }

                        if ($col != 'Z') {
                            $col++;
                        } else {
                            if ($precol != 'A') {
                                $precol = 'A';
                            } else {
                                break; // Não encontrado - ignorando
                            }
                            $col = 'A';
                        }

                    }
                }
                // Mapeado todos cabeçalhos, processando os registros
                $em = $this->getDoctrine()->getManager('gefra');
                $envio_repo = $em->getRepository(Envio::class);
                /* @var $juncao_repo JuncaoRepository */
                $juncao_repo = $em->getRepository(Juncao::class);
                $operador_repo = $em->getRepository(Operador::class);
                $sla_repo = $em->getRepository(SLA::class);

                $batchSize = max((int)$this->container->getParameter('app.bulk.batchsize'), 50);

                $j = $r1 = $r2 = 0; // counter
                while (true) {
                    $line++; // Indo para a linha de registros
                    // Ainda tem registros???
                    $col = $headers['GRM']; // jamais deveria deixar de existir o indice
                    $val = trim($spreadsheet->getActiveSheet()->getCell($col . $line));
                    if ($val != '') {
                        // Validando conteudo das colunas obrigatórias
                        foreach ($mandatoryColumns as $column => $entityAttribute) {
                            $col = $headers[$column]; // jamais deveria não existir o indice
                            $val = trim($spreadsheet->getActiveSheet()->getCell($col . $line));
                            if ($val == '') {
                                throw new Exception(
                                    sprintf("A coluna [%s] da linha [%d] deveria conter" .
                                        "informação de [%s], porém está em branco!",
                                        $col, $line, $column)
                                );
                            }
                        }

                        foreach ($mandatoryColumns as $column => $entityAttribute) {
                            $col = $headers[$column]; // jamais deveria não existir o indice
                            $val = trim($spreadsheet->getActiveSheet()->getCell($col . $line));
                            switch ($entityAttribute) {
                                case 'juncao':
                                    if (!$juncao = $juncao_repo->findOneBy(['codigo' => $val])) {
                                        continue;
                                        /*
                                        throw new \Exception(
                                            sprintf("A Junção para o código [%s] não foi encontrado. " .
                                                "Se a informação está correta é necessário criar o cadastro primeiro.",
                                                $entries[$i]['localização'])
                                        );
                                        */
                                    }
                                    break;

                                case 'operador':
                                    // Validando o operador
                                    if (!$operador = $operador_repo->findOneByCodigo($val)) {
                                        throw new \Exception(
                                            sprintf("Operador [%s] não foi encontrado. " .
                                                "Se a informação está correta é necessário criar o cadastro primeiro.",
                                                $val)
                                        );
                                    }
                                    break;

                                case 'qt_vol':
                                    if ((int)$val < 1) {
                                        throw new \Exception(
                                            sprintf("[%s] não é um valor para [%s] válido na coluna [%s] linha [%d]. " .
                                                "Registro será ignorado.", $val, $column, $col, $line)
                                        );
                                    }
                                    break;

                                case 'peso':
                                case 'valor':
                                    if (!(float)$val > 0) {
                                        throw new \Exception(
                                            sprintf("[%s] não é um valor para [%s] válido na coluna [%s] da linha [%d]. " .
                                                "Registro será ignorado.", $val, $column, $col, $line)
                                        );
                                    }
                                    break;

                                case 'dt_previsao_coleta':
                                case 'dt_varredura':
                                    if (!new \DateTime($val)) {
                                        throw new \Exception(
                                            sprintf("[%s] não é uma data válida para [%s] na coluna [%s] da linha [%d]. " .
                                                "Registro será ignorado.", $val, $column, $col, $line)
                                        );
                                    }
                                    break;

                                case 'grm':
                                    break;

                                default:
                                    throw new \Exception("Ooopss! Esqueci alguma coisa...");
                            }


                            // Todas as colunas obrigatórias validadas, Recuperando/Criando o Envio
                            // Verificando se já não existe em envio para a GRM (documento) passado
                            $col = $headers['grm']; // jamais deveria não existir o indice
                            $val = trim($spreadsheet->getActiveSheet()->getCell($col . $line));


                            if (!$envio = $envio_repo->findOneBy(['grm' => $val])) {
                                $r1++; // Contador de registros criados
                                // Novo Envio
                                $envio = new Envio();
                                // Calculando a data de entrega
                                if (!$sla = $sla_repo->findOneBy(['juncao' => $juncao, 'operador' => $operador])) {
                                    throw new Exception(
                                        sprintf("Não foi possivel calcular o prazo de entrega para a GRM [%s]." .
                                            "Verifique se a Junção [%s] possui PRAZO cadastrado para o " .
                                            "OPERADOR [%s]",
                                            $val, $juncao, $operador)
                                    );
                                }
                                // Calculando o prazo de entrega para um novo Envio
                                $dtprevisaocoletaindex = array_search('dt_previsao_coleta', $mandatoryColumns);
                                $col = $headers[$dtprevisaocoletaindex]; // jamais deveria deixar de existir o indice
                                $val = trim($spreadsheet->getActiveSheet()->getCell($col . $line));
                                $dt_coleta = new \DateTime($val);
                                $envio->setDtPrevisaoColeta($dt_coleta);
                                $prazo = $sla->getPrazo();
                                while ($prazo > 0) {
                                    $dt_coleta->add(new \DateInterval('P1D'));
                                    if ($dt_coleta->format('w') % 6 != 0) { // não é sábado nem domingo
                                        $prazo--;
                                    }
                                }
                                // @todo Verificar se tem algum feriando entre as datas de coleta e entrega
                                // if ($feriados = $feriado_repo->findBy(['dt_feriado' => $dt_coleta])) {
                                //     $dt_coleta->add(new \DateInterval('P'. $feriados->count() . 'D'));
                                // }

                                // Data Entrega Calculada
                                $envio->setDtPrevisaoEntrega($dt_coleta);
                                $envio->setJuncao($juncao)
                                    ->setOperador($operador);

                            } else {
                                $r2++; // Contador de registros atualizados
                            }
                            // existe, somente atualizar
                            $envio->setDtVarredura(new DateTime($spreadsheet->getActiveSheet()->getCell(
                                    $headers[array_search('dt_varredura', $mandatoryColumns)] . $line))
                            )
                                ->setQtVol($spreadsheet->getActiveSheet()->getCell(
                                    $headers[array_search('qt_vol', $mandatoryColumns)] . $line)
                                )
                                ->setValor($spreadsheet->getActiveSheet()->getCell(
                                    $headers[array_search('valor', $mandatoryColumns)] . $line)
                                )
                                ->setPeso($spreadsheet->getActiveSheet()->getCell(
                                    $headers[array_search('peso', $mandatoryColumns)] . $line)
                                );
                            // Demais informações
                            foreach ($updatingColumns as $column => $entityAttribute) {
                                $col = $headers[$column]; // jamais deveria deixar de existir o indice
                                $val = trim($spreadsheet->getActiveSheet()->getCell($col . $line));
                                if (strpos($entityAttribute, 'dt_') > -1) {
                                    $val = new \DateTime($val);
                                }
                                // Camecalizing
                                $methodName = 'set' . str_replace(" ", "", ucwords(str_replace("_", " ", $entityAttribute)));
                                $envio->{$methodName}($val);
                            }

                            // Fim do tratamento do Envio atual, persistindo
//                            $em->persist($envio);
                            if ($j++ > $batchSize) {
//                                $em->flush();
                                $j = 0;
                            }
                        }
                    } else {
                        // fim dos registros
                        // Relatorio basico
                        $msg = sprintf("%d registros criados. %d registros atualizados.", $r1, $r2);
                        break;
                    }

                }
                // $em->flush();

                throw new \Exception("Vá transar!");

                $j = 0; // counter
                $lote = date('YmdH');
                for ($i = 1; $i < count($entries); $i++) {
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
                    foreach (['valor', 'peso', 'volume', 'documento'] as $field) {
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
                        ->setOperador($operador);
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
            } catch (Exception $e) {
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
            return $this->redirectToRoute('gefra_envio_index');
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
        if (!$this->isCsrfTokenValid('delete' . $envio->getId(), $request->request->get('_token'))) {
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
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 0);
        $search_value = $request->get('search', ['value' => null])['value'];
        $orderNumColumn = $request->get('order', [0 => ['column' => 0]])[0]['column'];
        // somente uma coluna para ordenação aqui
        $orderColumn = array('e.dt_emissao_cte', 'e.grm','o.nome', 'j.nome','s.descricao',
            'e.cte', 'j.cidade', 'j.uf')[$orderNumColumn];
        $sortType = $request->get('order', [0 => ['dir' => 'DESC']])[0]['dir'];
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
            $search_value = StringUtils::slugify($search_value);
            $qb->orWhere(
                $qb->expr()->like('e.cte', '?1'),
                $qb->expr()->like('e.solicitacao', '?1'),
                $qb->expr()->like('e.grm', '?1'),
                $qb->expr()->like('j.canonical_name', '?1'),
                $qb->expr()->like('j.canonical_city', '?1')
            )->setParameters([1 => '%' . $search_value . '%']);
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
                $d['envio']['status'] = $last_ocorrencia->getTipo();
            } else {
                $d['envio']['status'] = 'NOVO';
            }

            // Status da Entrega
            $d['envio']['status_entrega'] = '';
            if ($envio->getDtEntrega() != null) { // Envio já foi entregue
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
     * @Route("/{id}", name="gefra_envio_get", methods="GET")
     */
    public function show(Envio $envio): Response
    {
        return $this->render('gefra_evio_show.html.twig', ['envio' => $envio]);
    }

    /**
     * @return Response
     * @Route("/arquivos/", name="gefra_envio_files_index")
     */
    public function showFiles(EnvioFileRepository $envioFileRepository): Response
    {
        $files = $envioFileRepository->findAll();
        $user_repo = $this->getDoctrine()->getManager()->getRepository(User::class);
        foreach ($files as $index => $file) {
            /* @var $file EnvioFile */
            $file->setUploadedBy($user_repo->find($file->getUploadedBy())->getName());
        }
        return $this->render('gefra/envio/files_index.html.twig', ['files' => $files]);
    }


    /**
     * @param Envio $envio
     * @return Response
     * @Route("/ocorrencia-envio/{id}", name="gefra_ocorrencia_list")
     */
    public function listOcorrenciaByEnvio(Envio $envio): Response
    {
        return new Response($this->renderView('gefra/envio/_ocorrencia_list.html.twig',
            ['ocorrencias' => $envio->getOcorrencias()])
        );
    }

}