<?php

namespace App\Controller\Localidade;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/localidade")
 */
class LocalidadeController extends Controller
{
    /**
     * @Route("/", name="localidade_home", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('localidade/index.html.twig');
    }


    /**
     * @return Response
     * @Route("/arquivo-modelo/{source}", name="localidade_samplefile")
     */
    public function downloadSampleCVS(Request $request, $source): Response
    {
        $filename = $this->getParameter('app.samples.dir');
        switch ($source) {
            case 'uf':
                $filename .= 'uf.sample.csv';
                $outputname = $this->get('translator')->trans('localidade.uf.sample-filename');
                break;
            case 'cidade':
                $filename .= 'local.sample.csv';
                $outputname = $this->get('translator')->trans('localidade.cidade.sample-filename');
                break;
            default:
                throw new NotFoundHttpException();
        }
        return $this->file($filename, $outputname . '.csv');
    }

}
