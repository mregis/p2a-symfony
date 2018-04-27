<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 22:31
 */

namespace App\Controller;


use App\Entity\OptionAttribute;
use App\Form\OptionAttributeType;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class OptionAttributeController extends Controller
{


    /**
     * @Route("/applicativos/opcoes/novo", name="new-option-attribute")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newOptionAttribute(Request $request)
    {
        $optionAttribute =  new OptionAttribute();
        $form = $this->createForm(OptionAttributeType::class, $optionAttribute);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                return $this->forward($this->generateUrl('list-apps'), 301);
            }
        }

        return $this->render('apps/new-option-attribute.html.twig', array('form' => $form->createView()));
    }
}