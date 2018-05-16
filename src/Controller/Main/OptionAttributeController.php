<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 22:31
 */

namespace App\Controller\Main;


use App\Entity\Main\OptionAttribute;
use App\Form\Main\OptionAttributeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OptionAttributeController
 * @package App\Controller\Main
 * @Route("/opcoes-aplicativo")
 */
class OptionAttributeController extends Controller
{


    /**
     * @Route("/novo", name="new-option-attribute")
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