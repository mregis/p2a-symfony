<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 09/02/2018
 * Time: 11:52
 */

namespace App\Controller\Main;

use App\Entity\Main\User;
use App\Repository\Main\UserRepository;
use App\Util\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class SecurityController
 * @package App\Controller\Main
 * @Route("/autenticacao")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="login")
     */
    public function index(Request $request, AuthenticationUtils $authUtils)
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
     * @Route("/esqueci-senha", name="security_password_recover")
     * @param Request $request
     * @return Response
     */
    public function forgotPassword(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('username', EmailType::class, array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                'attr' => array('placeholder' => 'forgot-password.e-mail-field.placeholder'),
                'label' => 'forgot-password.e-mail-field.label',
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
                $this->addFlash('success', 'forgot-password.flash.success');
                return $this->redirect($this->generateUrl('security_login_by_code'), 301);
            } else {
                $form->addError(new FormError('general_form_error'));
            }
        }
        return $this->render('security/forgot-password.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/autenticar-via-codigo", name="security_login_by_code")
     */
    public function loginByCode(Request $request)
    {
        $error = null;
        $form = $this->createFormBuilder()
            ->add('username', EmailType::class, array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                'attr' => array('placeholder' => 'forgot-password.e-mail-field.placeholder'),
                'label' => 'forgot-password.e-mail-field.label',
            ))

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
                    if ($user->getLastLogin()) {
                        $this->addFlash('success', 'change-password.flash.success');
                        return $this->redirect($this->generateUrl('security_password_redefine'), 301);
                    } else {
                        $this->addFlash('success', 'users.first-login');
                        $request->getSession()->set('myUser', $user);
                        // First login
                        return $this->redirect($this->generateUrl('security_complete_register'), 301);
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


    /**
     * @Route("/redefinir-senha", name="security_password_redefine")
     * @param Request $request
     * @return Response
     */
    public function redefinePassword(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $error = null;
        $form = $this->createFormBuilder()
            ->add('password_repeated', RepeatedType::class, array(
                'type' => PasswordType::class,
//                'invalid_message' => 'invalid_repeated_message',
                'options' => array('required' => true),
                'first_options' => array('label' => 'New Password'),
                'second_options' => array('label' => 'Retype the New Password'),
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => array('class' => 'btn-info btn-block'),
                'label' => 'forgot-password.submit'
            ))
            ->getForm();
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getUser();
                $password = $encoder->encodePassword($user, $data['password_repeated']);
                /* @var $user User */
                $user->setPassword($password);

                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($user);
                $objManager->flush();
                $this->addFlash('success', 'forgot-password.flash.success');
                return $this->redirect($this->generateUrl('home'), 301);
            }
            // Form valido mas não redirecionou, algo de errado tem...
            $error = new FormError('general_form_error');

        }
        return $this->render('security/redefine-pass.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    /**
     * @Route("/completar-cadastro", name="security_complete_register")
     * @param Request $request
     * @return Response
     */
    public function completeRegister(Request $request,
                                     UserPasswordEncoderInterface $encoder,
                                     TokenStorageInterface $tokenStorage)
    {
        $error = null;

        /* @var $preuser User */
        $preuser = $request->getSession()->get('myUser');

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, array(
                'data' => $preuser->getEmail(),
                'label' => 'users.email',
                'attr' => array('readonly' => true),
            ))
            ->add('username', TextType::class, array(
                'data' => $preuser->getUsername(),
                'label' => 'users.username',
                'attr' => array('readonly' => true),
            ))
            ->add('roles', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'data' => $this->get('translator')->trans('roles.names.' . $preuser->getRoles()[0]),
                'label' => 'users.profile',
                'attr' => array('readonly' => true),
            ))
            ->add('name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'data' => $preuser->getName(),
                'label' => 'users.name'
            ))

            ->add('password_repeated', RepeatedType::class, array(
                'invalid_message' => 'invalid_repeated_message',
                'required' => true,
                'type' => PasswordType::class,
                'options' => array('required' => true),
                'first_options' => array(
                    'label' => 'users.new-password-field',
                    'attr' => array('placeholder' => 'users.new-password-field')
                ),
                'second_options' => array(
                    'label' => 'users.re-password-label',
                    'attr' => array('placeholder' => 'users.re-password-field')
                ),
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'users.register-submit',
                'attr' => array('class' => 'btn-primary')
            ))
            ->getForm();

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                /* @var $user_repo UserRepository */
                $user_repo = $this->getDoctrine()->getRepository(User::class);
                $user = $user_repo->loadUserByUsername($data['username']);
                $password = $encoder->encodePassword($user, $data['password_repeated']);
                /* @var $user User */
                $user->setPassword($password)
                    ->setName($data['name'])
                    ->setLastLogin(new \DateTime());
                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($user);
                $objManager->flush();
                // authenticating
                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    'main',
                    $user->getRoles()
                );
                $tokenStorage->setToken($token);
                $this->addFlash('success', 'users.security_complete_register.success');
                return $this->redirect($this->generateUrl('home'), 301);
            } else {
                $error = new FormError('general_form_error');
            }
        }
        return $this->render('security/security_complete_register.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    /**
     * @Route("/alterar-senha", name="security_change_password")
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $error = null;
        $form = $this->createFormBuilder()
            ->add('old-password', PasswordType::class, array(
                'required' => true,
                'constraints' => array(new UserPassword()),
                'label' => 'forgot-password.old-password.label'
            ))
            ->add('password_repeated', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array('required' => true),
                'first_options' => array('label' => 'forgot-password.new-password.label'),
                'second_options' => array('label' => 'forgot-password.re-password.label'),
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => array('class' => 'btn-primary btn-block'),
                'label' => 'forgot-password.submit'
            ))
            ->getForm();
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getUser();
                $password = $encoder->encodePassword($user, $data['password_repeated']);
                /* @var $user User */
                $user->setPassword($password);

                $objManager = $this->getDoctrine()->getManager();
                $objManager->persist($user);
                $objManager->flush();
                $this->addFlash('success', 'reset-password.flash.success');

                return $this->redirect($this->generateUrl('home'), 301);
            }
            // Form valido mas não redirecionou, algo de errado tem...
            $error = new FormError('general_form_error');

        }
        return $this->render('security/redefine-pass.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }

    /**
     * @param Request $request
     * @Route("/check-last-login", name="security_check_last_login")
     * @param Request $request
     * @return Response
     *
     */
    public function checkLastLogin(Request $request)
    {
        /* @var $user User */
        if (!$user = $this->getUser()) {
            throw new AuthenticationCredentialsNotFoundException();
        }
        if ($user->getLastLogin() == null) {
            $request->getSession()->set('myUser', $user);
            return $this->redirectToRoute('security_complete_register');
        }
        $user->setLastLogin(new \DateTime());
        $oManager = $this->getDoctrine()->getManager();
        $oManager->persist($user);
        $oManager->flush();

        return $this->redirectToRoute('home');
    }
}