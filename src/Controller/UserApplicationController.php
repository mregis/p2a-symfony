<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 22:31
 */

namespace App\Controller;


use App\Entity\Application;
use App\Entity\OptionAttribute;
use App\Entity\User;
use App\Entity\UserApplication;
use App\Form\OptionAttributeType;
use App\Form\UserApplicationType;
use Doctrine\DBAL\Types\TextType;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Form\Extension\Core\Type as Types;

class UserApplicationController extends Controller
{


    /**
     * @Route("/utilizadores/{id}opcoes/novo", name="new-user-app-option-attribute")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newOptionAttribute(Request $request)
    {
        $optionAttribute = new OptionAttribute();
        $form = $this->createForm(OptionAttributeType::class, $optionAttribute);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                return $this->forward($this->generateUrl('list-apps'), 301);
            }
        }

        return $this->render('apps/new-option-attribute.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/utilizadores/{id}/apps", name="list-user-apps")
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
     * @Route("/utilizadores/{id}/apps/novo", name="new-user-app")
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
            array('cancel_url' => $this->generateUrl('list-user-apps', array('id' => $id)))
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                // There's a new Application to be asserted, go to Config definitions
                return $this->redirectToRoute('new-user-app-step-two',
                    array('id' => $id, 'application' => $form->getData()->getApplication()->getId())
                );
            }
        }

        return $this->render('users/new-user-application.html.twig',
            array('form' => $form->createView(), 'e_user' => $e_user));
    }


    /**
     * @Route("/utilizadores/{id}/apps/{application}/opcoes", name="new-user-app-step-two")
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
            array('cancel_url' => $this->generateUrl('list-user-apps', array('id' => $id)))
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($userApplication);
                $objManager->flush();
                $this->addFlash('success', 'user-application.new-flash.success');
                return $this->redirect($this->generateUrl('list-user-apps', array('id'=>$id)), 301);
            }
        }

        return $this->render('users/new-user-application-step-two.html.twig',
            array('form' => $form->createView(), 'e_user' => $e_user, 'e_app' => $e_app)
        );
    }

    /**
     * @Route("/utilizadores/app/editar/{id}/", name="edit-user-app")
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
        $e_app = $userApplication->getApplication();
        $form = $this->createForm(UserApplicationType::class, $userApplication,
            array('cancel_url' => $this->generateUrl('list-user-apps', array('id' => $e_user->getId())))
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($userApplication);
                $objManager->flush();
                $this->addFlash('success', 'user-application.edit-flash.success');
                return $this->redirect($this->generateUrl('list-user-apps', array('id'=>$e_user->getId())), 301);
            }
        }

        return $this->render('users/new-user-application-step-two.html.twig', array('form' => $form->createView()
        , 'e_user' => $e_user, 'e_app' => $e_app));
    }
}