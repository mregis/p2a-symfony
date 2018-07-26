<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 08/02/2018
 * Time: 16:36
 */

namespace App\Controller;

use App\Entity\Main\Application;
use App\Entity\Main\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class DefaultController
 * @package App\Controller
 * Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        /* @var $user User */
        $user = $this->getUser();
        $userapps = new ArrayCollection();
        foreach ($user->getUserApplication() as $userapp) {
            $userapps->add($userapp->getApplication());
        }
        if ($authChecker->isGranted('ROLE_SUPERADMIN')) {
            $userapps = $this->getDoctrine()->getRepository(Application::class)->findBy(['isActive' => true]);
        }

        return $this->render('home.html.twig', array('userapps' => $userapps));
    }

    /**
     * @Route("/404dev", name="page-not-found")
     */
    public function PageNotFound()
    {
        throw $this->createNotFoundException('Teste de pagina 404');
    }

    /**
     * @Route("/404test", name="error-404-page")
     * @return Response
     */
    public function Page404test()
    {
        return $this->render('tests/error404.html.twig', array());
    }

}