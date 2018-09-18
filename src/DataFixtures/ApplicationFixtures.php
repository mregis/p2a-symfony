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

class ApplicationFixtures extends Fixture
{
    public function load(ObjectManager $oManager)
    {
        $stringTypes = ['TextType', 'TextareaType', 'EmailType', 'UrlType', 'TelType', 'SearchType',];
        $numberTypes = ['IntegerType', 'MoneyType', 'NumberType', 'PercentType',];
        $dateTypes = ['DateType', 'DateTimeType', 'TimeType',];
        $appsAlias = [1 => 'Facebook', 'Twitter', 'Instagram', 'Tumbler', 'Tinder'];
        foreach ($appsAlias as $k => $app) {
            /* @var $application Application */
            $application = new Application();
            $application->setName('Application Number ' . $k)
                ->setIsActive(true)
                ->setAlias($app)
                ->setUri("http://www." . strtolower($app) . ".com");

            // String Attributes
            foreach (array_rand($stringTypes, 3) as $typesKey) {
                $optionAttribute = new OptionAttribute();
                $optionAttribute->setName('Option ' . str_replace('Type', '', $stringTypes[$typesKey]))
                    ->setDefaultValue('Default Value for Option ' . $typesKey)
                    ->setRequired(($typesKey % 2 == 1))
                    ->setType($stringTypes[$typesKey]);
                $application->addOption($optionAttribute);
            }

            foreach (array_rand($numberTypes, 2) as $typesKey) {
                $optionAttribute = new OptionAttribute();
                $optionAttribute->setName('Option ' . str_replace('Type', '', $numberTypes[$typesKey]))
                    ->setDefaultValue(rand(0,100))
                    ->setRequired(($typesKey % 2 == 1))
                    ->setType($numberTypes[$typesKey]);
                $application->addOption($optionAttribute);
            }

            $oManager->persist($application);
        }
        $oManager->flush();
    }

}