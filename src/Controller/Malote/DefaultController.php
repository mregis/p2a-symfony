<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 19/10/2018
 * Time: 15:30
 */

namespace App\Controller\Malote;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/malote")
 */
class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="malote_home", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('malote/index.html.twig');
    }

    /**
     * @return Response
     * @Route("/arquivo-modelo/{source}", name="malote_samplefile")
     */
    public function downloadSampleFile(Request $request, $source): Response
    {
        $filename = $this->getParameter('app.samples.dir') . $source . '.sample';
        if (is_file($filename)) {
            $outputname = $this->get('translator')->trans('malote.'. $source .'.sample-filename');
            return $this->file($filename, $outputname);
        }
        throw new NotFoundHttpException();
    }
}