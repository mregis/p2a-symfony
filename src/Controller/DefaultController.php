<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 08/02/2018
 * Time: 16:36
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index()
    {
        return $this->render('home.html.twig', array());
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
        return $this->render('error404.html.twig', array());
    }

}