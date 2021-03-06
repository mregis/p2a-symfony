<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Main\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProfileFixtures extends Fixture
{
    // Order matters
    private $basicRoles = ['ROLE_SUPERADMIN', 'ROLE_ADMIN', 'ROLE_MASTER', 'ROLE_USER'];

    public function load(ObjectManager $oManager)
    {

        foreach ($this->basicRoles as $exp => $role)
        {
            $profile = new Profile();
            $profile->setName($role)
                ->setIsActive(true)
                ->setDescription('roles.descriptions.' . $role) // For use with Translators
                ->setLevel( pow(2, $exp)); // Like Unix CHMOD
                $oManager->persist($profile);
        }
        $oManager->flush();
    }

}