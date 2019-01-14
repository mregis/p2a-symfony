<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 08/02/2018
 * Time: 16:36
 */

namespace App\Controller;

use App\Entity\Main\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
	
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
    public function index(Request $request)
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/404dev", name="page-not-found")
     */
    public function PageNotFound()
    {
        throw $this->createNotFoundException('Teste de pagina 404');
    }

    /**
     * @Route("/5xxdev", name="internal-server-error")
     */
    public function InternalServerError()
    {
        throw new HttpException(500, 'Teste de pagina 5xx');
    }

    /**
     * @Route("/404test", name="error-404-page")
     * @return Response
     */
    public function Page404test()
    {
        return $this->render('tests/error404.html.twig', array());
    }

    /**
     * @Route("/5xxtest", name="error-5xx-page")
     * @return Response
     */
    public function Page5xxtest()
    {
        return $this->render('tests/error5xx.html.twig', array());
    }
}