<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 08/02/2018
 * Time: 20:48
 */

namespace App\Controller\Main;


use App\Entity\Main\User;
use App\Form\Main\UserType;
use App\Util\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/utilizadores")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="main_user_index")
     */
    public function listUsers(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user_list = $repository->loadUsersByUser($this->getUser());
        return $this->render('users/list-users.html.twig', array('user_list' => $user_list));
    }

    /**
     * @Route("/{id}/editar", name="main_user_edit")
     */
    public function editUser($id, Request $request)
    {
        $error = null;
        if (!$e_user = $this->getDoctrine()->getRepository(User::class)->find($id)) {
            throw $this->createNotFoundException('not-found-error');
        }

        /* @var User */
        $myUser = $this->getUser();
        $form = $this->createForm(UserType::class, $e_user, ['userCaller' => $myUser]);

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($e_user);
                $objManager->flush();
                $this->addFlash('success', 'users.edit-flash.success');
                return $this->redirect($this->generateUrl('main_user_index'), 301);
            } else {
                $error = new FormError('general_form_error');
            }
        }

        return $this->render('users/main_user_edit.html.twig', array('user' => $e_user, 'form' => $form->createView(), 'error' => $error));
    }


    /**
     * @Route("/novo", name="main_user_new")
     * @param Request $request
     * @return Response
     */
    public function newUser(Request $request)
    {
        $e_user = new User();
        /* @var User */
        $myUser = $this->getUser();
        $form = $this->createForm(UserType::class, $e_user, ['userCaller' => $myUser]);
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $e_user->setPassword('blank');
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($e_user);
                $objManager->flush();
                $this->addFlash('success', 'users.new-flash.success');
                return $this->redirect($this->generateUrl('main_user_index'), 301);
            }
        }

        return $this->render('users/new-user.html.twig',
            array('user' => $e_user, 'form' => $form->createView()));
    }

    /**
     * Send a redefine password message to an User
     * @Route("/email-redefinir-senha", name="main_user_password_redefiine_email")
     * @param Request $request
     * @return array
     */
    public function postRedefinePasswordEmailByIdAction(Request $request)
    {
        $message = 'basic-error';
        $statusMode = 'danger';
        $title = $this->get('translator')->trans('resetting.email.subject');
        if ($user = $this->getDoctrine()->getRepository(User::class)->find($request->get('id'))) {
            $minlastlogin = '-' . $this->container->getParameter('minlastlogin') . ' min';
            if ($user->getPasswordRequestedAt() < new \DateTime($minlastlogin)) {
                $tokenGenerator = new TokenGenerator($this->container->getParameter('mintokenlength'),
                    $this->container->getParameter('maxtokenlength'), true);
                $user->setConfirmationToken($tokenGenerator->generateToken());
                $user->setPasswordRequestedAt(new \DateTime());
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($user);
                $objManager->flush();
                $code = $user->getConfirmationToken();
                if ($code) {
                    $msg = (new \Swift_Message($this->get('translator')->trans('resetting.email.subject')))
                        ->setFrom(array($this->getParameter('mail.address')))
                        ->setTo(array($user->getEmail()))
                        ->setBody($this->renderView('mailing/forgot-password.txt.twig', array(
                            'user' => $user,
                            'code' => $code
                        )), 'text/plain')
                        ->addPart($this->renderView('mailing/forgot-password.html.twig',
                            array('user' => $user, 'code' => $code)), 'text/html');

                    $this->get('mailer')->send($msg);
                    $message = $this->get('translator')->trans('resetting.email.sent-success', ['%email%'=> $user->getEmail()]);
                    $statusMode = 'success';
                }
            } else {
                $message = $this->get('translator')->trans('resetting.many-tries',['%time%' => $this->container->getParameter('minlastlogin')]);
                $statusMode = 'warning';
            }
        } else {
            $message = $this->get('translator')->trans('oops-error');
        }

        return $this->json(array('message' => $message, 'status' => $statusMode, 'title' => $title), Response::HTTP_OK);
    }
}