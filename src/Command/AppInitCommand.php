<?php

namespace App\Command;

use App\DataFixtures\ApplicationFixtures;
use App\DataFixtures\BancoFixtures;
use App\DataFixtures\ProfileFixtures;
use App\DataFixtures\TipoEnvioStatusFixtures;
use App\DataFixtures\TransportadoraFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\User\User;

class AppInitCommand extends Command
{
    protected static $defaultName = 'app:init';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Incializa a aplicação em um novo ambiente');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;
        $io->writeln('<info>Inciando a configuração da aplicação para um novo ambiente</info>');

        /* @var $container Container */
        $container = $this->getApplication()->getKernel()->getContainer();

        /* @var $doctrine Registry */
        $doctrine = $container->get('doctrine');

        // Key => Value pair mean connection => db
        $mandatories = [
            'default' => 'sso', // DATABASE_URL
            'agencia' => 'agencias', // DATABASE2_URL
            'localidade' => 'localidade', // DATABASE3_URL
            'gefra' => 'gefra', // DATABASE4_URL
        ];
        $io->writeln("====Verificando a existência das bases de dados e respectivas conexões:");

        $dropDbCommand = $this->getApplication()->find('doctrine:schema:drop');
        $createDbCommand = $this->getApplication()->find('doctrine:database:create');
        $updateSchemaCommand = $this->getApplication()->find('doctrine:schema:update');

        // Limpando as bases se elas existirem
        $mns = $doctrine->getManagerNames();
        $io->writeln("\n==== Removendo os schemas/bases de dados se elas existirem:");
        foreach ($mns as $mn) {
            // Extraindo o nome do EntityManager
            $mn = strtr($mn, array('doctrine' => '', '.' => '', 'orm' =>'', '_entity_manager' => ''));
            $io->writeln(sprintf('<<<<<<comment>%s</comment>>>>>>', $mn));
            // Executando comandos de remoção de base de dados
            $dropDbArguments = array(
                'command' => 'doctrine:schema:drop',
                '--force' => true,
                '--em' => $mn,
            );
            $dropDbInput = new ArrayInput($dropDbArguments);
            $returnCode = $dropDbCommand->run($dropDbInput, $output);
        }

        /* @var $em ObjectManager */
        foreach ($mandatories as $connection => $database) {
            $io->writeln(
                sprintf("\n==== <comment>Database</comment> <info>%s</info>, <comment>Conexão</comment> <info>%s</info>",
                    $database,
                    $connection));
            /* @var $conn \Doctrine\DBAL\Connection */
            if ($conn = $doctrine->getConnection($connection)) {
                $db = $conn->getDatabase();
                $dbDriver = $conn->getDriver()->getName();
                $io->writeln(
                    sprintf("Base de Dados <info>%s</info> encontrada na conexão <info>%s</info> " .
                        "utilizando driver <info>%s</info>!",
                    $db, $connection, $dbDriver
                ));
                if ($dbDriver == 'pdo_sqlite') {
                    $io->note("Bases de dados em SQLite não serão recriadas através deste comando. Ignorando...");
                    continue;
                }

                // Executando comandos de criação de base de dados
                $createDbArguments = array(
                    'command' => 'doctrine:database:create',
                    '--if-not-exists' => true,
                    '--connection' => $connection,
                );
                $createDbInput = new ArrayInput($createDbArguments);
                $returnCode = $createDbCommand->run($createDbInput, $output);

            } else {
                $io->error(sprintf("A Conexão <info>%s</info> não foi encontrada! " .
                    "Verifique as configurações do arquivo <info>.env</info>",
                    $connection));
            }
        }



        $io->writeln("\n==== Recriando/Atualizando os schemas das bases de dados indicadas:");
        foreach ($mns as $mn) {
            // Extraindo o nome do EntityManager
            $mn = strtr($mn, array('doctrine' => '', '.' => '', 'orm' =>'', '_entity_manager' => ''));
            $io->writeln(sprintf('<<<<<<comment>%s</comment>>>>>>', $mn));
            // Executando comandos de criação de base de dados
            $createDbArguments = array(
                'command' => 'doctrine:schema:update',
                '--force' => true,
                '--em' => $mn,
            );
            $updateSchemaInput = new ArrayInput($createDbArguments);
            $returnCode = $updateSchemaCommand->run($updateSchemaInput, $output);
        }

        $this->loadFixtures(); // Carregar

        $io->success('Aplicação Iniciada');
    }

    public function loadFixtures()
    {
        /* @var $container Container */
        $container = $this->getApplication()->getKernel()->getContainer();
        /* @var $doctrine Registry */
        $doctrine = $container->get('doctrine');

        $this->io->writeln('<fg=black;bg=cyan>===== Iniciando carga de ' .
            'dados basicos para funcionamento da aplicação Painel ======</>');

        $this->io->writeln("\t Usando Banco de Dados <fg=green;options=bold>default</>");

        $oManager = $doctrine->getManager('default');
        // ProfileFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>Perfil</>... ");
        $profileBaseFixtures = new ProfileFixtures();
        $this->io->write('Carregando novos dados ... ');
        $profileBaseFixtures->load($oManager);
        $this->io->writeln('OK.');
        // ApplicationFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>Aplicações</>... ");
        $profileBaseFixtures = new ApplicationFixtures();
        $this->io->write('Carregando novos dados ... ');
        $profileBaseFixtures->load($oManager);
        $this->io->writeln('OK.');
        // UserFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>Usuario</>... ");
        $userBaseFixtures = new UserFixtures($container->get('security.password_encoder'));
        $this->io->write('OK. Carregando novos dados ... ');
        $userBaseFixtures->load($oManager);
        $this->io->writeln('OK.');

        $this->io->writeln("\t Usando Banco de Dados <fg=green;options=bold>agencias</>");
        $oManager = $doctrine->getManager('agencia');
        // BancoFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>Banco</>... ");
        $bancoBaseFixtures = new BancoFixtures();
        $this->io->write('OK. Carregando novos dados ... ');
        $bancoBaseFixtures->load($oManager);
        $this->io->writeln('OK.');

        $this->io->writeln("\t Usando Banco de Dados <fg=green;options=bold>gefra</>");
        $oManager = $doctrine->getManager('gefra');
        // TransportadoraFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>Transportadora</>... ");
        $transportadoraBaseFixtures = new TransportadoraFixtures();
        $this->io->write('OK. Carregando novos dados ... ');
        $transportadoraBaseFixtures->load($oManager);
        $this->io->writeln('OK.');

        // TipoEnvioStatusFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>TipoEnvioStatus</>... ");
        $tipoEnvioBaseFixtures = new TipoEnvioStatusFixtures();
        $this->io->write('OK. Carregando novos dados ... ');
        $tipoEnvioBaseFixtures->load($oManager);
        $this->io->writeln('OK.');

        $this->io->writeln('<fg=black;bg=cyan>===== Carga de ' .
            'dados concluída ======</>');
    }
}
