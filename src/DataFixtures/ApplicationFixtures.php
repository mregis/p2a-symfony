<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\OptionAttribute;
use App\Entity\Application;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ApplicationFixtures extends Fixture
{
    public function load(ObjectManager $oManager)
    {
        $types = ['TextType', 'TextareaType', 'EmailType', 'IntegerType', 'MoneyType',
            'NumberType', 'UrlType', 'TelType', 'DateType', 'DateTimeType', 'TimeType', 'PercentType',
            'SearchType', 'BirthdayType', 'CheckboxType', 'FileType'];
        $appsAlias = [1 => 'Facebook', 'Twitter', 'Instagram', 'Tumbler', 'Tinder'];
        foreach ($appsAlias as $k => $app) {
            /* @var $application Application */
            $application = new Application();
            $application->setName('Application Number ' . $k)
                ->setIsActive(true)
                ->setAlias($app);

            $rand_keys = array_rand($types, 5);

            foreach ($rand_keys as $typesKey) {
                $optionAttribute = new OptionAttribute();
                $optionAttribute->setName('Option ' . str_replace('Type', '', $types[$typesKey]))
                    ->setDefaultValue('Default Value for Option ' . $typesKey)
                    ->setRequired(($typesKey % 2 == 1))
                    ->setType($types[$typesKey]);

                $application->addOption($optionAttribute);
            }

            $oManager->persist($application);
        }
        $oManager->flush();
    }

}