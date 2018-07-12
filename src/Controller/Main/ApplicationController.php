<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 12/04/2018
 * Time: 18:16
 */

namespace App\Controller\Main;

use App\Entity\Main\Application;
use App\Form\Main\ApplicationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/aplicativos")
 */
class ApplicationController extends Controller
{

    /**
     * @Route("/", name="list-apps")
     */
    public function listApplications(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Application::class);
        $app_list = $repository->findAll();
        return $this->render('apps/list-apps.html.twig', array('app_list' => $app_list));
    }

    /**
     * @Route("/{id}/editar", name="edit-app")
     */
    public function editApplication($id, Request $request)
    {
        $error = null;
        if (!$e_app = $this->getDoctrine()->getRepository(Application::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }

        $form = $this->createForm(ApplicationType::class, $e_app);

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $options = $form->get('options')->getData();
                $e_app->setOptions($options);
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($e_app);
                $objManager->flush();
                $this->addFlash('success', 'application.edit-flash.success');
                return $this->redirect($this->generateUrl('list-apps'), 301);
            } else {
                $error = new FormError('general_form_error');
            }
        }

        return $this->render('apps/edit-app.html.twig', array('myapp' => $e_app, 'form' => $form->createView(), 'error' => $error));
    }

    /**
     * @Route("/novo", name="new-app")
     * @param Request $request
     * @return Response
     */
    public function newApplication(Request $request)
    {
        $e_app = new Application();
        $form = $this->createForm(ApplicationType::class, $e_app);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($e_app);
                $objManager->flush();
                $this->addFlash('success', 'application.new-flash.success');
                return $this->redirect($this->generateUrl('list-apps'), 301);
            }
        }

        return $this->render('apps/new-app.html.twig',
            array('myapp' => $e_app, 'form' => $form->createView()));
    }
}