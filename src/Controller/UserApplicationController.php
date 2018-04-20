<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 22:31
 */

namespace App\Controller;


use App\Entity\OptionAttribute;
use App\Entity\User;
use App\Entity\UserApplication;
use App\Form\OptionAttributeType;
use App\Form\UserApplicationType;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

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
     * @param Request $request
     * @return RedirectResponse|Response
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
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newUserApp($id, Request $request)
    {
        /* @var $e_user User */
        if (!$e_user = $this->getDoctrine()->getRepository(User::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }
        $userApplication = new UserApplication();
        $userApplication->setUser($e_user);
        $form = $this->createForm(UserApplicationType::class, $userApplication);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                return $this->forward($this->generateUrl('list-apps'), 301);
            }
        }

        return $this->render('users/new-user-application.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/utilizadores/apps/editar/{id}", name="edit-user-app")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editUserApp($id, Request $request)
    {
        /* @var $e_user User */
        if (!$userApplication = $this->getDoctrine()->getRepository(UserApplication::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }
        $form = $this->createForm(UserApplicationType::class, $userApplication);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                return $this->forward($this->generateUrl('list-user-apps'), 301);
            }
        }

        return $this->render('users/edit-user-application.html.twig', array('form' => $form->createView()));
    }
}