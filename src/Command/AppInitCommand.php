<?php

namespace App\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;

class AppInitCommand extends Command
{
    protected static $defaultName = 'app:init';

    protected function configure()
    {
        $this
            ->setDescription('Incializa a aplicação em um novo ambiente');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln('<info>Inciando a configuração da aplicação para um novo ambiente</info>');

        /* @var $container Container */
        $container = $this->getApplication()->getKernel()->getContainer();

        /* @var $doctrine Registry */
        $doctrine = $doctrine = $container->get('doctrine');

        // Key => Value pair mean connection => db
        $mandatories = [
            'default' => 'sso', // DATABASE_URL
            'agencia' => 'agencias', // DATABASE2_URL
            'localidade' => 'localidade', // DATABASE3_URL
            'gefra' => 'gefra', // DATABASE4_URL
        ];
        $io->writeln("====Verificando a existência das bases de dados e respectivas conexões:");

        $createDbCommand = $this->getApplication()->find('doctrine:database:create');
        $updateSchemaCommand = $this->getApplication()->find('doctrine:schema:update');

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

        $mns = $doctrine->getManagerNames();

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

        $io->success('Aplicação Iniciada');
    }
}
