<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 28/08/2018
 * Time: 13:29
 */

namespace App\Command;


use App\Entity\Gefra\Envio;
use App\Entity\Gefra\EnvioFile;
use App\Entity\Gefra\Juncao;
use App\Entity\Gefra\Operador;
use App\Entity\Gefra\SLA;
use App\Entity\Gefra\TipoEnvioStatus;
use App\Entity\Localidade\Feriado;
use App\Form\Gefra\EnvioFileType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Command\LockableTrait;

class processEnvioFileCommand extends ContainerAwareCommand
{
    use LockableTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $envioFileManager;

    /**
     * @var Xlsx
     */
    protected $reader;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(EntityManagerInterface $gefraManager, EntityManagerInterface $localidadeManager)
    {
        $this->envioFileManager = $gefraManager;
        $this->feriadoManager = $localidadeManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:enviofile:process')
            ->setDescription('Processa as planilhas XLS de Envios')
            ->setHelp('Este comando processa todos os arquivos de envios carregados. ')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('O commando está sendo executado em outro processo.');
            return 0;
        }

        $this->output = $output;
        // outputs multiple lines to the console (adding "\n" at the end of each line)

        $this->output->writeln([
            'Processando Arquivos de Envios',
            '============',
            'Verificando a existência de arquivos não processados',
        ], OutputInterface::VERBOSITY_NORMAL);



        // the value returned by someMethod() can be an iterator (https://secure.php.net/iterator)
        // that generates and returns the messages with the 'yield' PHP keyword
        $files = $this->findEnvioFiles();

        if (count($files) > 0) {
            $output->writeln(sprintf('Há %d arquivo a ser processados.', count($files)), OutputInterface::VERBOSITY_NORMAL);
            $this->processEnvioFile($files);
        } else {
            $output->writeln('Não há arquivos a serem processados.');
        }
        $this->release();
    }

    /**
     * @return EnvioFile[]
     */
    protected function findEnvioFiles()
    {
        $envioFile_repo = $this->envioFileManager->getRepository(EnvioFile::class);
        return $envioFile_repo->findBy(['status' => EnvioFileType::NEW_SEND]);
    }

    /**
     * @param $files
     * @throws \Exception
     */
    protected function processEnvioFile($files)
    {
        $dest_dir = $this->getContainer()->getParameter('enviofiles.directory');
        foreach ($files as $k => $enviofile) {
            /* @var $enviofile EnvioFile */
            $filename = $dest_dir . DIRECTORY_SEPARATOR . basename($enviofile->getPath());
            if (is_file($filename)) {
                // Para quando estiver usando linha de comando em Windows no ambiente de desenvolvimento
                // e o webserver estiver rodando em Linux não se deve usar o mesmo caminho
                $enviofile->setStatus(EnvioFileType::IN_PROGRESS)
                    ->setProcessingStartedAt(new \DateTime())
                    ;
                $this->envioFileManager->persist($enviofile);
                $this->envioFileManager->flush();
                $enviofile->setPath($filename);
                if ($this->processEnvio($enviofile) == true) {
                    $result = EnvioFileType::FINISHED_OK;
                } else {
                    /* @todo Decidir se desfaz os cadastros de Envios criados */
                    $result = EnvioFileType::FINISHED_ERROR;
                }
                $enviofile->setStatus($result)
                    ->setProcessingEndedAt(new \DateTime());
                $this->envioFileManager->persist($enviofile);
                $this->envioFileManager->flush();
            } else {
                /*
                $this->output->writeln(
                    sprintf("O arquivo [%s] nao foi encontrado no caminho [%s]",
                        $enviofile->getHashid(), $enviofile->getPath())
                );
                */
                throw new \Exception(sprintf("O arquivo [%s] nao foi encontrado no caminho [%s]",
                    $enviofile->getHashid(), $enviofile->getPath()));
            }
        }
    }

    /**
     * @param EnvioFile $file
     * @return void
     * @throws Exception
     * @throws \Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function processEnvio(EnvioFile $file): bool
    {
        $reader = $this->getReader();
        $reader->setReadDataOnly(true);
        try {
            $spreadsheet = $reader->load($file->getPath());
            $activeSheet = $spreadsheet->getActiveSheet();

            // @todo Converter para tornar estas informações dinamicas e personalizáveis
            // Linha dos cabeçalhos encontrada, determinando quais colunas devem ser lidas
            $mandatoryColumns = [
                "GRM" => "grm",
                "JUNÇÃO" => "juncao",
                "OP. LOGÍSTCIO" => "operador",
                "VOL" => "qt_vol",
                "PESO" => "peso",
                "VALOR" => "valor",
                "COLETA" => "dt_previsao_coleta"];
            $updatingColumns = ["CTE" => "cte",
                "VARREDURA" => "dt_varredura",
                "EMISSÃO CTE" => "dt_emissao_cte",
                "DATA ENTREGA" => "dt_entrega",
                "NOME RECEBEDOR" => "recebedor",
                "CÓD FUNCIONAL" => "doc_recebedor"];
            $headers = []; // Irá armanezar os Metadados de mapeamento Planilha x Entity
            // Procurando os cabeçalhos para criar mapeamento Planilha x Entity
            $line = $col = $headline = null;
            $this->output->write("Procurando os cabeçalhos... ", false, OutputInterface::VERBOSITY_VERBOSE);
            foreach ($activeSheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $line = $row->getRowIndex();
                foreach ($cellIterator as $cell) {
                    $val = strtoupper($cell->getValue());
                    if (array_key_exists($val, $mandatoryColumns) || array_key_exists($val, $updatingColumns)) { // linha dos cabeçalhos encontrada
                        $headers[$val] = $cell->getColumn();
                    }
                }
                if (count($headers) > 0 || $line > 5) { // Tentar por no máximo 5 linhas
                    break;
                }
            }

            // Encontrou?
            if (count($headers) == 0) {
                throw new \Exception("O arquivo parece não conter informações de envios. Processo abortado.");
            }
            $this->output->writeln("OK! Encontrados.", OutputInterface::VERBOSITY_VERBOSE);
            $this->output->write("Verificando se todos os cabeçalhos obrigatórios estão presentes... ",
                false, OutputInterface::VERBOSITY_VERBOSE);
            // Todos os cabeçalhos necessários estão presentes?
            if (count($headers) != count($mandatoryColumns) + count($updatingColumns)) {
                throw new \Exception("O arquivo parece não conter informações de envios. Processo abortado.");
            }
            $this->output->writeln("OK! Todos os cabeçalhos presentes.", OutputInterface::VERBOSITY_VERBOSE);
            // Mapeado todos cabeçalhos, processando os registros
            $em = $this->envioFileManager;
            $envio_repo = $em->getRepository(Envio::class);
            /* @var $juncao_repo JuncaoRepository */
            $juncao_repo = $em->getRepository(Juncao::class);
            $operador_repo = $em->getRepository(Operador::class);
            $sla_repo = $em->getRepository(SLA::class);
            /* @var $feriado_repo FeriadoRepository */
            $feriado_repo = $this->feriadoManager->getRepository(Feriado::class);

            $r1 = $r2 = 0; // counter

            $status_novo = $em->getRepository(TipoEnvioStatus::class)->findOneByName('NOVO');
            // Transportadora
            $current_data = array();
            foreach ($activeSheet->getRowIterator($line + 1) as $row) {
                $col = $headers['GRM']; // jamais deveria deixar de existir o indice
                $cellIterator = $row->getCellIterator();
                $cellIterator->seek($col);
                $grm = trim($cellIterator->current()->getValue());
                if ($grm != '') {
                    $this->output->writeln(
                        sprintf("Verificando a GRM [%s] presente na linha [%d]", $grm, $row->getRowIndex()),
                        OutputInterface::VERBOSITY_NORMAL
                    );
                    // Validando conteudo das colunas obrigatórias
                    foreach ($mandatoryColumns as $column => $entityAttribute) {
                        $col = $headers[$column]; // jamais deveria não existir o indice
                        $cellIterator->seek($col);
                        $val = trim($cellIterator->current()->getValue());
                        if ($val == '') {
                            throw new \Exception(
                                sprintf("A coluna [%s] da linha [%d] deveria conter " .
                                    "informação de [%s], porém está em branco!",
                                    $col, $row->getRowIndex(), $column)
                            );
                        }
                        $this->output->writeln(
                            sprintf("\t%s = %s", $column, (is_scalar($val) ? $val : gettype($val) )),
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                    }
                    foreach ($mandatoryColumns as $column => $entityAttribute) {
                        $col = $headers[$column]; // jamais deveria não existir o indice
                        $cellIterator->seek($col);
                        $val = trim($cellIterator->current()->getValue());
                        switch ($entityAttribute) {
                            case 'juncao':
                                /* @var $juncao Juncao */
                                if (!$juncao = $juncao_repo->findOneBy(['codigo' => $val])) {
                                    throw new \Exception(
                                        sprintf("A Junção para o código [%s] não foi encontrado. " .
                                            "Se a informação está correta é necessário criar o cadastro primeiro.",
                                            $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tJunção de destino = [%s:%s - %s/%s]",
                                        $juncao->getCodigo(),
                                        $juncao->getNome(),
                                        $juncao->getCidade(),
                                        $juncao->getUf()
                                    ),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'operador':
                                // Validando o operador
                                /* @var $operador Operador */
                                if (!$operador = $operador_repo->findOneByCodigo($val)) {
                                    throw new \Exception(
                                        sprintf("Operador [%s] não foi encontrado. " .
                                            "Se a informação está correta é necessário criar o cadastro primeiro.",
                                            $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tOperador = [%s]",$operador->getNome()),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'qt_vol':
                                if ((int)$val < 1) {
                                    throw new \Exception(
                                        sprintf("[%s] não é um valor para [%s] válido na coluna [%s] linha [%d]. " .
                                            "Registro será ignorado.", $val, $column, $col, $line)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tVolumes = [%s]", $val),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'peso':
                            case 'valor':
                                if (!(float)$val > 0) {
                                    throw new \Exception(
                                        sprintf("[%s] não é um valor para [%s] válido na coluna [%s] da linha [%d]. " .
                                            "Registro será ignorado.", $val, $column, $col, $line)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\t%s = [%s]", $column, $val),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'dt_previsao_coleta':
                            case 'dt_varredura':
                                // Identificando o formato lido
                                $v = null;
                                // tratando o valor
                                $val = preg_replace("#[^\d\-]#", "", $val);
                                if ($val != '') {
                                    if (is_object($val)) {
                                        $v = $val;
                                    } elseif (!is_numeric($val)) {
                                        $v = preg_replace("#[^\d\-]#", "", str_replace("/", "-", $val));
                                    } elseif ((int)$val == $val) {
                                        $v = Date::excelToDateTimeObject($val);
                                    }
                                }
                                $val = $v;
                                if ($val != null && !$val instanceof \DateTime) {
                                    throw new \Exception(
                                        sprintf("[%s] não é uma data válida para [%s] na coluna [%s] da linha [%d]. " .
                                            "Registro será ignorado.", $val, $column, $col, $line)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\t%s = [%s]",
                                        $column,
                                        $val instanceof \DateTime ? $val->format('d/m/Y') : $val
                                    ),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'grm':
                                break;

                            default:
                                throw new \Exception("Ooopss! Esqueci alguma coisa...");
                        }
                        $current_data[$entityAttribute] = $val;
                    }

                    // Todas as colunas obrigatórias validadas, Recuperando/Criando o Envio
                    // Verificando se já não existe em envio para a GRM (documento) passado
                    if (!$envio = $envio_repo->findOneBy(['grm' => $grm])) {
                        $r1++; // Contador de registros criados
                        // Novo Envio
                        $this->output->writeln(
                            "Criando elemento Envio para armazenar no BD.",
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                        $envio = new Envio();
                        $envio->setGrm($grm)
                            ->setTransportadora($file->getTransportadora())
                            ->setStatus($status_novo)
                            ->setLote($file->getHashid());
                        // Calculando a data de entrega
                        $this->output->writeln(
                            "Calculando a data de entrega...",
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                        if (!$sla = $sla_repo->findOneBy(['juncao' => $juncao, 'operador' => $operador])) {
                            throw new Exception(
                                sprintf("Não foi possivel calcular o prazo de entrega para a GRM [%s]." .
                                    "Verifique se a Junção [%s] possui PRAZO cadastrado para o " .
                                    "OPERADOR [%s]",
                                    $val, $juncao, $operador)
                            );
                        }
                        // Calculando o prazo de entrega para um novo Envio
                        $dt_coleta = $current_data['dt_previsao_coleta']; // Tem que existir
                        $envio->setDtPrevisaoColeta($dt_coleta);
                        $prazo = $sla->getPrazo();
                        $dt_previsao_entrega = clone $dt_coleta;
                        while ($prazo > 0) {
                            $dt_previsao_entrega->add(new \DateInterval('P1D'));
                            if ($dt_previsao_entrega->format('w') % 6 != 0) { // não é sábado nem domingo
                                $prazo--;
                            }
                        }

                        $datas = [];
                        $this->output->writeln(
                            sprintf("\tVerificando feriados entre %s e %s... ",
                                $dt_coleta->format('d/m/Y'),
                                $dt_previsao_entrega->format('d/m/Y')
                                ),
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                        // @todo Verificar se tem algum feriando entre as datas de coleta e entrega
                        foreach ($feriado_repo->findAllByInterval(
                            $dt_coleta,
                            $dt_previsao_entrega,
                            $juncao->getCidade(),
                            $juncao->getUf()
                        ) as $feriado) {
                            $datas[$feriado->getDtFeriado()->format('Ymd')] = $feriado->getDtFeriado();
                        }
                        $this->output->writeln(
                            sprintf("%s feriados ",
                                (count($datas) > 0 ? 'Há ' . count($datas) : 'Não há')
                            ),
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                        foreach($datas as $data => $feriado) {
                            if ($feriado->format('w') % 6 != 0) { // não é sábado nem domingo
                                $dt_previsao_entrega->add(new \DateInterval('P1D')); // Adicionando mais 1 dia ao prazo
                            }
                            // Verificando se o Prazo agora não está em um sábado ou domingo
                            while ($dt_previsao_entrega->format('w') % 6 == 0) { // caindo no fim de semana
                                $dt_previsao_entrega->add(new \DateInterval('P1D')); // Adicionando mais 1 dia ao prazo
                            }
                        }
                        $this->output->write(
                            sprintf("Data de Previsão de Entrega é %s",
                                $dt_previsao_entrega->format('d/m/Y')
                            ),
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                        // Data Entrega Calculada
                        $envio->setDtPrevisaoEntrega($dt_previsao_entrega);
                        $envio->setJuncao($juncao)
                            ->setOperador($operador);

                    } else {
                        $r2++; // Contador de registros atualizados
                    }
                    // existe, somente atualizar os dados
                    // @todo criar Listener para alterações do Envio
                    // Data de Varredura
                    // $val = $current_data['dt_varredura'];
                    // $envio->setDtVarredura($val);
                    // Volumes
                    $val = $current_data['qt_vol'];
                    $envio->setQtVol($val);
                    // Valor
                    $val = $current_data['valor'];
                    $envio->setValor($val);
                    // Peso
                    $val = $current_data['peso'];
                    $envio->setPeso($val);
                    // Demais informações
                    foreach ($updatingColumns as $column => $entityAttribute) {
                        $col = $headers[$column]; // jamais deveria deixar de existir o indice
                        $val = trim($cellIterator->seek($col)->current()->getValue());
                        if (strpos($entityAttribute, 'dt_') > -1) {
                            $v = null;
                            $val = preg_replace("#[^\d\-]#", "", str_replace("/", "-", $val));
                            if ($val != '') {
                                if (is_object($val)) {
                                    $v = $val;
                                } elseif (!is_numeric($val)) {
                                    $v = new \DateTime($val);
                                } elseif ((int)$val == $val) {
                                    $v = Date::excelToDateTimeObject($val);
                                }
                            }
                            $val = $v;
                            if ($val != null && !$val instanceof \DateTime) {
                                throw new \Exception(
                                    sprintf("[%s] não é uma data válida para [%s] na coluna [%s] da linha [%d]. " .
                                        "Registro será ignorado.", $val, $column, $col, $line)
                                );
                            }
                        }
                        // Camecalizing
                        $methodName = 'set' . str_replace(" ", "", ucwords(str_replace("_", " ", $entityAttribute)));
                        $envio->{$methodName}($val);
                    }
                    $this->output->writeln("Finalizando tratamento... ", OutputInterface::VERBOSITY_VERBOSE);
                    // Fim do tratamento do Envio atual, persistindo
                    $em->persist($envio);
                    $this->output->writeln(
                        sprintf("OK. Envio para a GRM [%s] finalizado.", $grm),
                        OutputInterface::VERBOSITY_VERBOSE
                    );
                } else {
                    // Não tem informação de GRM nesta linha
                }
            }

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->output->writeln($msg, OutputInterface::VERBOSITY_NORMAL);
            return false;
        }

        return true;
    }


    /**
     * @return Xlsx
     */
    protected function getReader()
    {
        if ($this->reader == null) {
            $this->reader = new Xlsx();
            $this->reader->setReadDataOnly(true);
            $this->reader->getReadEmptyCells(false);
        }
        return $this->reader;
    }
}