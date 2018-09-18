<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 18/07/2018
 * Time: 17:20
 */

namespace App\Controller\Gefra;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller\Gefra
 * @Route("/gefra")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="gefra_home", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('gefra/index.html.twig');
    }

    /**
     * @return Response
     * @Route("/arquivo-modelo/{source}", name="gefra_samplefile")
     */
    public function downloadSampleFile(Request $request, $source): Response
    {
        $filename = $this->getParameter('app.samples.dir') . $source . '.sample';
        if (is_file($filename)) {
            $outputname = $this->get('translator')->trans('gefra.'. $source .'.sample-filename');
            return $this->file($filename, $outputname);
        }
        throw new NotFoundHttpException();
    }
}