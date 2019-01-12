<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 22:31
 */

namespace App\Controller\Main;


use App\Entity\Main\Application;
use App\Entity\Main\OptionAttribute;
use App\Entity\Main\User;
use App\Entity\Main\UserApplication;
use App\Form\Main\OptionAttributeType;
use App\Form\Main\UserApplicationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type as Types;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserApplicationController
 * @package App\Controller
 * @Route("/utilizador-app")
 */
class UserApplicationController extends Controller
{


    /**
     * @Route("/{id}/opcoes/novo", name="main_userapplication_optionattribute_new")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newOptionAttribute(Request $request)
    {
        $optionAttribute = new OptionAttribute();
        $form = $this->createForm(OptionAttributeType::class, $optionAttribute);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                return $this->forward($this->generateUrl('main_application_index'), 301);
            }
        }

        return $this->render('apps/new-option-attribute.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/apps", name="main_userapplication_index")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function listUserApps($id, Request $request)
    {
        /* @var $e_user User */
        if (!$e_user = $this->getDoctrine()->getRepository(User::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }

        $app_list = $e_user->getUserApplication();
        return $this->render('users/list-user-apps.html.twig', array('app_list' => $app_list, 'e_user' => $e_user));
    }


    /**
     * @Route("/{id}/app/novo", name="main_userapplication_new")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function newUserAppStepOne($id, Request $request)
    {
        /* @var $e_user User */
        if (!$e_user = $this->getDoctrine()->getRepository(User::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }
        $userApplication = new UserApplication();
        $userApplication->setUser($e_user);
        $form = $this->createForm(UserApplicationType::class, $userApplication,
            array('cancel_url' => $this->generateUrl('main_userapplication_index', array('id' => $id)))
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                // There's a new Application to be asserted, go to Config definitions
                return $this->redirectToRoute('main_userapplication_new_step_two',
                    array('id' => $id, 'application' => $form->getData()->getApplication()->getId())
                );
            }
        }

        return $this->render('users/new-user-application.html.twig',
            array('form' => $form->createView(), 'e_user' => $e_user));
    }


    /**
     * @Route("/{id}/{application}/opcoes", name="main_userapplication_new_step_two")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function newUserAppStepTwo($id, $application, Request $request)
    {
        /* @var $e_user User */
        if (!$e_user = $this->getDoctrine()->getRepository(User::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }
        /* @var $e_app Application */
        if (!$e_app = $this->getDoctrine()->getRepository(Application::class)->find($application)) {
            throw $this->createNotFoundException('not-found-error');
        }

        $userApplication = new UserApplication();
        $userApplication->setUser($e_user);
        $userApplication->setApplication($e_app);

        $userApplication->setOptions($e_app->getOptions());

        $form = $this->createForm(UserApplicationType::class, $userApplication,
            array('cancel_url' => $this->generateUrl('main_userapplication_index', array('id' => $id)))
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($userApplication);
                $objManager->flush();
                $this->addFlash('success', 'user-application.new-flash.success');
                return $this->redirect($this->generateUrl('main_userapplication_index', array('id'=>$id)), 301);
            }
        }

        return $this->render('users/new-user-application-step-two.html.twig',
            array('form' => $form->createView(), 'e_user' => $e_user, 'e_app' => $e_app)
        );
    }

    /**
     * @Route("/{id}/editar", name="main_userapplication_edit")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editUserApp($id, Request $request)
    {
        /* @var $userApplication UserApplication */
        if (!$userApplication = $this->getDoctrine()->getRepository(UserApplication::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }
        /* @var $e_user User */
        $e_user = $userApplication->getUser();
        /* @var $e_app Application */
        $e_app = $userApplication->getApplication();
        $userApplication->addOptions($e_app->getOptions());
        $form = $this->createForm(UserApplicationType::class, $userApplication,
            array('cancel_url' => $this->generateUrl('main_userapplication_index', array('id' => $e_user->getId())))
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($userApplication);
                $objManager->flush();
                $this->addFlash('success', 'user-application.edit-flash.success');
                return $this->redirect($this->generateUrl('main_userapplication_index', array('id'=>$e_user->getId())), 301);
            }
        }

        return $this->render('users/new-user-application-step-two.html.twig', array('form' => $form->createView()
        , 'e_user' => $e_user, 'e_app' => $e_app));
    }
}