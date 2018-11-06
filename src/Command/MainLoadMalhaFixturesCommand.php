<?php

namespace App\Command;

use App\DataFixtures\ApplicationFixtures;
use App\DataFixtures\BancoFixtures;
use App\DataFixtures\MalhaFixtures;
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

class MainLoadMalhaFixturesCommand extends Command
{
    protected static $defaultName = 'app:main:load:malha';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Carrega uma lista de Malhas para ser utilizado dentro do App Malote');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;
        $io->writeln('<info>Inciando carga de dados</info>');

        /* @var $container Container */
        $container = $this->getApplication()->getKernel()->getContainer();

        /* @var $doctrine Registry */
        $doctrine = $container->get('doctrine');

        if (!$oManager = $doctrine->getManager('malote')) {
            $io->error(sprintf("A Conexão <info>malote</info> não foi encontrada! " .
                "Verifique as configurações do arquivo <info>.env</info>"));
        }
        // ProfileFixtures
        $this->io->write("\t Trabalhando tabela <fg=green;options=bold>Malha</>... ");
        $profileBaseFixtures = new MalhaFixtures();
        $this->io->write('Carregando novos dados ... ');
        $profileBaseFixtures->load($oManager);
        $this->io->writeln('OK.');

        $io->success('Dados carregados');
    }

}
