<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 19/10/2018
 * Time: 15:30
 */

namespace App\Controller\Malote;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/malote")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="malote_home", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('malote/index.html.twig');
    }
}