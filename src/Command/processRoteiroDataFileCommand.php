<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 28/08/2018
 * Time: 13:29
 */

namespace App\Command;


use App\Entity\Agencia\Agencia;
use App\Entity\Main\Transportadora;
use App\Entity\Main\Unidade;
use App\Entity\Main\UploadDataFile;
use App\Entity\Malote\Malha;
use App\Entity\Malote\Roteiro;
use App\Repository\Agencia\AgenciaRepository;
use App\Repository\Main\UploadDataFileRepository;
use App\Util\StringUtils;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Command\LockableTrait;

class processRoteiroDataFileCommand extends ContainerAwareCommand
{
    use LockableTrait;

    /* @var EntityManagerInterface */
    protected $default_em;

    /* @var EntityManagerInterface */
    protected $malote_em;

    /* @var EntityManagerInterface */
    protected $agencias_em;

    /**
     * @var Xlsx
     */
    protected $reader;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('app:process:roteirofile')
            ->setDescription('Processa as planilhas XLS de Roteiros')
            ->setHelp('Este comando processa todos os arquivos de roteiros carregados. ')
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
            'Processando Arquivos de Roteiros',
            '============',
            'Verificando a existência de arquivos não processados',
        ], OutputInterface::VERBOSITY_NORMAL);

        // Carregando o entityManager default

        /* @var $container Container */
        $container = $this->getApplication()->getKernel()->getContainer();

        /* @var $doctrine Registry */
        $doctrine = $container->get('doctrine');

        $this->default_em = $doctrine->getManager();
        $this->malote_em = $doctrine->getManager('malote');
        $this->agencias_em = $doctrine->getManager('agencia');

        // the value returned by someMethod() can be an iterator (https://secure.php.net/iterator)
        // that generates and returns the messages with the 'yield' PHP keyword
        $files = $this->findRoteiroDataFiles();

        if (count($files) > 0) {
            $output->writeln(
                (count($files) > 1 ?
                    sprintf('Há %d arquivos a serem processados', count($files)) :
                    'Há 1 arquivo a ser processado'
                ),
                OutputInterface::VERBOSITY_NORMAL
            );
            $this->processRoteiroDataFile($files);
        } else {
            $output->writeln('Não há arquivo a ser processado.');
        }
        $this->release();
    }

    /**
     * @return UploadDataFile[]
     */
    protected function findRoteiroDataFiles()
    {

        /* @var $upload_datafile_repo UploadDataFileRepository */
        $upload_datafile_repo = $this->default_em->getRepository(UploadDataFile::class);
        return $upload_datafile_repo->findBy(
            ['status' => UploadDataFile::NEW_SEND, 'type' => 'ROTEIRO_DATA_FILE']
        );
    }

    /**
     * @param $files
     * @throws \Exception
     */
    protected function processRoteiroDataFile($files)
    {
        $dest_dir = $this->getContainer()->getParameter('uploaddatafiles.directory');
        foreach ($files as $k => $roteiro_datafile) {
            /* @var $roteiro_datafile UploadDataFile */
            $filename = $dest_dir . DIRECTORY_SEPARATOR . basename($roteiro_datafile->getPath());
            if (is_file($filename)) {
                // Para quando estiver usando linha de comando em Windows no ambiente de desenvolvimento
                // e o webserver estiver rodando em Linux não se deve usar o mesmo caminho
                $roteiro_datafile->setStatus(UploadDataFile::IN_PROGRESS)
                    ->setProcessingStartedAt(new \DateTime())
                    ;
                $this->default_em->persist($roteiro_datafile);
                $this->default_em->flush();
                $roteiro_datafile->setPath($filename);
                if ($this->processRoteiro($roteiro_datafile) == true) {
                    $result = UploadDataFile::FINISHED_OK;
                } else {
                    /* @todo Decidir se desfaz os cadastros de Roteiros criados */
                    $result = UploadDataFile::FINISHED_ERROR;
                }
                $roteiro_datafile->setStatus($result)
                    ->setProcessingEndedAt(new \DateTime());
                $this->default_em->persist($roteiro_datafile);
                $this->default_em->flush();
            } else {
                throw new \Exception(sprintf("O arquivo [%s] nao foi encontrado no caminho [%s]",
                    $roteiro_datafile->getHashid(), $roteiro_datafile->getPath()));
            }
        }
    }

    /**
     * @param UploadDataFile $file
     * @return void
     * @throws \Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function processRoteiro(UploadDataFile $file): bool
    {
        $reader = $this->getReader();
        $reader->setReadDataOnly(true);
        try {
            $spreadsheet = $reader->load($file->getPath());
            $activeSheet = $spreadsheet->getActiveSheet();

            // @todo Converter para tornar estas informações dinamicas e personalizáveis
            // Linha dos cabeçalhos encontrada, determinando quais colunas devem ser lidas
            $mandatoryColumns = [
                "JUNÇÃO" => "juncao",
                "UNIDADE" => "unidade",
                "TRANSPORTADORA" => "transportadora",
                "FREQUENCIA" => "frequencia",
                "LOTE" => "lote",
                "CD" => "cd",
                "ROTA" => "rota",
                "MALHA" => "malha"
            ];
            $updatingColumns = [];
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
                throw new \Exception("O arquivo parece não conter informações de roteiros. Processo abortado.");
            }
            $this->output->writeln("OK! Cabeçalho encontrado.", OutputInterface::VERBOSITY_VERBOSE);
            $this->output->write("Verificando se todos os cabeçalhos obrigatórios estão presentes... ",
                false, OutputInterface::VERBOSITY_VERBOSE);
            // Todos os cabeçalhos necessários estão presentes?
            if (count($headers) != count($mandatoryColumns) + count($updatingColumns)) {
                throw new \Exception("O arquivo parece não conter informações de roteiros. Processo abortado.");
            }
            $this->output->writeln("OK! Todos os cabeçalhos presentes.", OutputInterface::VERBOSITY_VERBOSE);
            // Mapeado todos cabeçalhos, processando os registros

            /* @var $roteiro_repo RoteiroRepository */
            $roteiro_repo = $this->malote_em->getRepository(Roteiro::class);
            /* @var $agencia_repo AgenciaRepository */
            $agencia_repo = $this->agencias_em->getRepository(Agencia::class);

            $r1 = $r2 = 0; // counter

            // Transportadora
            $current_data = array();
            foreach ($activeSheet->getRowIterator($line + 1) as $row) {
                $col = $headers['JUNÇÃO']; // jamais deveria deixar de existir o indice
                $cellIterator = $row->getCellIterator();
                $cellIterator->seek($col);
                $agencia = trim($cellIterator->current()->getValue());
                if ($agencia != '') {
                    $this->output->writeln(
                        sprintf("Verificando a JUNCAO [%s] presente na linha [%d]", $agencia, $row->getRowIndex()),
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
                                /* @var $agencia Agencia */
                                if (!$agencia = $agencia_repo->findOneBy(['codigo' => $val])) {
                                    throw new \Exception(
                                        sprintf("A Agencia para o código [%s] não foi encontrado. " .
                                            "Se a informação está correta é necessário criar o cadastro primeiro.",
                                            $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tAgencia de destino = [%s:%s - %s/%s]",
                                        $agencia->getCodigo(),
                                        $agencia->getNome(),
                                        $agencia->getCidade(),
                                        $agencia->getUf()
                                    ),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'unidade':
                                // Validando a Unidade
                                /* @var $unidade Unidade */
                                if (!$unidade =
                                    $this->default_em->getRepository(Unidade::class)
                                        ->findOneByNomeCanonico(StringUtils::slugify($val))) {
                                    throw new \Exception(
                                        sprintf("Unidade [%s] não foi encontrada. " .
                                            "Se a informação está correta é necessário criar o cadastro primeiro.",
                                            $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tUnidade = [%s]",$unidade->getNome()),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'transportadora':
                                // Validando a Transportadora
                                /* @var $transportadora Transportadora */
                                if (!$transportadora =
                                    $this->default_em->getRepository(Transportadora::class)
                                        ->findOneByNomeCanonico(StringUtils::slugify($val))) {
                                    throw new \Exception(
                                        sprintf("Transportadora [%s] não foi encontrada. " .
                                            "Se a informação está correta é necessário criar o cadastro primeiro.",
                                            $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tTransportadora = [%s]",$transportadora->getNome()),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'malha':
                                // Validando a Malha
                                /* @var $malha Malha */
                                if (!$malha =
                                    $this->malote_em->getRepository(Malha::class)
                                        ->findOneByNomeCanonico(StringUtils::slugify($val))) {
                                    throw new \Exception(
                                        sprintf("Malha [%s] não foi encontrada. " .
                                            "Se a informação está correta é necessário criar o cadastro primeiro.",
                                            $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\tMalha = [%s]",$malha->getNome()),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'frequencia':
                                // Validando a Frequencia
                                $val = StringUtils::slugify($val);
                                if (!in_array($val, ['alternado', 'diario'])) {
                                    throw new \Exception(
                                        sprintf("Frequencia [%s] é inválida.", $val)
                                    );
                                }

                                $this->output->writeln(
                                    sprintf("\tFrequencia = [%s]", $val),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                break;

                            case 'lote':
                            case 'cd':
                                // Validando Lote ou CD
                                if (!(int) $val < 0) {
                                    throw new \Exception(
                                        sprintf("%s [%s] é inválido.", ucfirst($entityAttribute), $val)
                                    );
                                }
                                $this->output->writeln(
                                    sprintf("\t%s = [%s]", ucfirst($entityAttribute), $val),
                                    OutputInterface::VERBOSITY_VERBOSE
                                );
                                $$entityAttribute = $val;
                                break;

                            case 'rota':
                                $rota = $val;
                                break;

                            default:
                                throw new \Exception("Ooopss! Esqueci alguma coisa...");
                        }
                    }

                    // Todas as colunas obrigatórias validadas, Recuperando/Criando o Roteiro
                    // Verificando se já não existe Roteiro para a Agencia e Unidade passados
                    if (!$roteiro = $roteiro_repo->findOneBy(['agencia' => $agencia, 'unidade' => $unidade])) {
                        $r1++; // Contador de registros criados
                        // Novo Roteiro
                        $this->output->writeln(
                            "Criando elemento Roteiro para armazenar no BD.",
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                        $roteiro = new Roteiro();
                        $roteiro->setAgencia($agencia)
                            ->setUnidade($unidade)
                            ->setMalha($malha)
                            ->setTransportadora($transportadora)
                            ->setLote($lote)
                            ->setRota($rota)
                            ->setCd($cd);

                    } else {
                        $r2++; // Contador de registros atualizados
                    }
                    // existe, somente atualizar os dados
                    // @todo criar Listener para alterações do Roteiro

                    $this->output->writeln("Finalizando tratamento... ", OutputInterface::VERBOSITY_VERBOSE);
                    // Fim do tratamento do Roteiro atual, persistindo
                    $this->malote_em->persist($roteiro);
                    $this->output->writeln(
                        sprintf("OK. Roteiro [%s] finalizado.", $roteiro),
                        OutputInterface::VERBOSITY_VERBOSE
                    );
                } else {
                    // Não tem informação de Roteiro nesta linha
                }
            }

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->output->writeln($msg, OutputInterface::VERBOSITY_NORMAL);
            return false;
        }
        $this->malote_em->flush();
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