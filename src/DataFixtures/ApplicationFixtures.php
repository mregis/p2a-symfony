<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Main\OptionAttribute;
use App\Entity\Main\Application;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Routing\RouterInterface;

class ApplicationFixtures extends Fixture
{

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }


    public function load(ObjectManager $oManager)
    {
        #### Criando entrada para Aplicativo de Localidade
        $application = new Application();
        $application->setName('Cadastro de Locais e Feriados')
            ->setAlias('Locais')
            ->addOption(
                (new OptionAttribute())
                    ->setName('icone ')
                    ->setDefaultValue('fas fa-globe')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->addOption(
                (new OptionAttribute())
                    ->setName('bg-color ')
                    ->setDefaultValue('bg-apps-3')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->addOption(
                (new OptionAttribute())
                    ->setName('border-color ')
                    ->setDefaultValue('border-apps-3')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->setUri($this->router->generate('localidade_home'));

        $oManager->persist($application);

        #### Criando entrada para Aplicativo de Agência
        $application = new Application();
        $application->setName('Cadastro de Agências')
            ->setAlias('Agências')
            ->addOption(
                (new OptionAttribute())
                    ->setName('icone ')
                    ->setDefaultValue('fas fa-university')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->addOption(
                (new OptionAttribute())
                    ->setName('bg-color ')
                    ->setDefaultValue('bg-apps-2')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->addOption(
                (new OptionAttribute())
                    ->setName('border-color ')
                    ->setDefaultValue('border-apps-2')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->setUri($this->router->generate('agencia_home'));

        #### Criando entrada para Aplicativo de Espelhos de Malotes
        $application = new Application();
        $application->setName('Gestão Entregas Fracionadas')
            ->setAlias('SISGEFRA')
            ->addOption(
                (new OptionAttribute())
                    ->setName('icone ')
                    ->setDefaultValue('fas fa-road')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->addOption(
                (new OptionAttribute())
                    ->setName('bg-color ')
                    ->setDefaultValue('bg-apps-0')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->addOption(
                (new OptionAttribute())
                    ->setName('border-color ')
                    ->setDefaultValue('border-apps-0')
                    ->setRequired(true)
                    ->setType('TextType')
            )
            ->setUri($this->router->generate('gefra_home'));

        $oManager->flush();
    }

}