<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->passwordEncoder = $encoder;
    }

    public function load(ObjectManager $oManager)
    {
        // SuperAdmin creation
        if ($profile = $oManager->getRepository(Profile::class)->findOneBy(array('level' => 1))) {
            $user = new User();
            $user->setName('Administrator')
                ->setEmail('marcos.regis@address.com.br')
                ->setUsername('marcos.regis')
                ->setPassword($this->passwordEncoder->encodePassword($user, 'blablabla'))
                ->setProfile($profile);

            $oManager->persist($user);

        }

        $oManager->flush();

    }

    public function getDependencies()
    {
        return array(
            ProfileFixtures::class,
            ApplicationFixtures::class,
        );
    }

}