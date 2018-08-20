<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 14/08/2018
 * Time: 11:33
 */

namespace App\EventListener;



use App\Entity\Main\Application;
use App\Entity\Main\User as AppUser;
use App\Entity\Main\UserApplication;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class onLoginSuccessSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onLoginSuccess(AuthenticationEvent $event)
    {
        $authToken = $event->getAuthenticationToken();
        /* @var $user \App\Entity\Main\User */
        $user = $authToken->getUser();
        if ($user instanceof AppUser) {
            $roles = $user->getRoles();

            if (in_array('ROLE_SUPERADMIN', $roles) && $user->getUserApplication()->count() < 1) {
                $apps = $this->entityManager->getRepository(Application::class)->findBy(['isActive' => true]);
                foreach ($apps as $app) {
                    $ua = new UserApplication();
                    $ua->setApplication($app)
                        ->setUser($user);
                    $user->getUserApplication()->add($ua);
                }

                $authToken->setUser($user);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // constant for security.authentication.success
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onLoginSuccess',
        );
    }
}