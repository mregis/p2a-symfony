<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 09/02/2018
 * Time: 11:52
 */

namespace App\Controller;

use App\Entity\User;
use App\Provider\UserProvider;
use App\Util\TokenGenerator;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class SecurityController extends Controller
{
    /**
     * @Route("/entrar", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();
        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/esqueci-senha", name="forgot-password")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function forgotPassword(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('username', EmailType::class, array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                'attr' => array('placeholder' => 'forgot-password.e-mail-field-placeholder', 'class' => 'form-control'),
                'label' => 'forgot-password.e-mail-field-label',
            ))
            ->getForm();
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $repository = $this->getDoctrine()->getRepository(User::class);
                $user = $repository->findOneBy(['email' => $data['username'], 'isActive' => true]);
                $minlastlogin = '-' . $this->container->getParameter('minlastlogin') . ' min';
                if($user &&
                    $user->getPasswordRequestedAt() < (new \DateTime($minlastlogin))
                ) {
                    $tokenGenerator = new TokenGenerator($this->container->getParameter('mintokenlength'),
                        $this->container->getParameter('maxtokenlength'), true);
                    $user->setConfirmationToken($tokenGenerator->generateToken());
                    $user->setPasswordRequestedAt(new \DateTime());
                    $objManager = $this->getDoctrine()->getManager();
                    $objManager->persist($user);
                    $objManager->flush();
                    $code = $user->getConfirmationToken();
                    if ($code) {
                        $message = (new \Swift_Message($this->get('translator')->trans('resetting.email.subject')))
                            ->setFrom(array($this->getParameter('mail.address')))
                            ->setTo(array($user->getEmail()))
                            ->setBody($this->renderView('mailing/forgot-password.txt.twig', array(
                                'user' => $user,
                                'code' => $code
                            )), 'text/plain')
                            ->addPart($this->renderView('mailing/forgot-password.html.twig',
                                array('user' => $user, 'code' => $code)), 'text/html');

                        $this->get('mailer')->send($message);
                    }
                }
                // Show Success Message whenever occours
                $request->getSession()->getFlashBag()->add('success', 'forgot-password.flash.success');
                return $this->redirect($this->generateUrl('login-by-code'), 301);
            } else {
                $form->addError(new FormError('general_form_error'));
            }
        }
        return $this->render('security/forgot-password.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/autenticar-via-codigo", name="login-by-code")
     */
    public function loginByCode(Request $request, TokenStorageInterface $tokenStorage)
    {
        $error = null;
        $form = $this->createFormBuilder()
            ->add('code', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(),
                    new Assert\Length(
                        array('min' => $this->getParameter('mintokenlength'),
                            'max' => $this->getParameter('maxtokenlength'))
                    )),
                'attr' => array('placeholder' => 'verify-code.code-field-placeholder',
                    'maxlength' => $this->getParameter('maxtokenlength'),
                    ),
                'label' => 'verify-code.code-field-label',
                'required' => true,
            ))
            ->getForm();
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findOneBy(array('confirmationToken' => $data['code']));
                if ($user && $user->isEnabled()) {

                    $token = new UsernamePasswordToken(
                        $user,
                        $user->getPassword(),
                        'main',
                        $user->getRoles()
                    );
                    $tokenStorage->setToken($token);
                    $request->getSession()->getFlashBag()->add('success', 'change-password.flash.success');
                    if ($user->getLastLogin()) {
                        return $this->redirect($this->generateUrl('redefine_password'), 301);
                    } else {
                        // First login
                        return $this->redirect($this->generateUrl('complete_register'), 301);
                    }
                } else {
                    $form->get('code')->addError(new FormError(
                        $this->get('translator')->trans('verify-code.flash.error'))
                    );
                }
            }
            // Form valido mas não redirecionou, algo de errado tem...
            $error = new FormError('general_form_error');

        }

        return $this->render('security/login-by-code.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

}