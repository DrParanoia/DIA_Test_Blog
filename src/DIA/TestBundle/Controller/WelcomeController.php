<?php

namespace DIA\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use DIA\TestBundle\Entity\User;

class WelcomeController extends Controller {

    public function indexAction() {
        $request = $this->getRequest();
        $session = $request->getSession();

        $locale = $session->getLocale();

        return $this->redirect($locale);
    }

    public function registerUser() {

    }

    public function regComplete() {
        
    }

    public function welcomeAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $security = $this->get('security.context');

        $locale = $session->getLocale();

        $auth = $security->isGranted('IS_AUTHENTICATED_FULLY');

        if($auth) {
            return $this->redirect($this->generateUrl('blog_main'));
        }
 
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
 
        return $this->render('DIATestBundle:Welcome:welcome.html.twig', array(
            // last username entered by the user
            'last_username'     => $session->get(SecurityContext::LAST_USERNAME),
            'error'             => $error,
            'locale'            => $locale
        ));
        //return $this->render('DIATestBundle:Welcome:welcome.html.twig', array('test' => $test));
    }

    public function registerAction() {
        $response = Array();

        $security = $this->get('security.context');
        $request = $this->getRequest();

        $auth = $security->isGranted('IS_AUTHENTICATED_FULLY');

        if($auth) {
            return $this->redirect($this->generateUrl('blog_main'));
        }
        if($request->getMethod() == 'POST') {
            $username = trim($request->get('username'));
            $password = trim($request->get('password'));
            $re_password = trim($request->get('re_password'));

            $first_name = trim($request->get('first_name'));
            $last_name = trim($request->get('last_name'));

            $error = false;
            if(empty($username)) {
                $response['errors']['username']['empty'] = true;
                $error = true;
            } else {
                $em = $this->getDoctrine()->getEntityManager();

                if($original = $em->getRepository('DIATestBundle:User')->findOneByUsername($username)) {
                    $response['errors']['username']['taken'] = true;
                    $error = true;                   
                }
            }
            if(empty($password)) {
                $response['errors']['password']['empty'] = true;
                $error = true;
            }
            if(empty($re_password)) {
                $response['errors']['re_password']['empty'] = true;
                $error = true;
            }
            if(!$error) {
                if($password != $re_password) {
                    $response['errors']['password']['match'] = true;
                    $error = true;
                }
            }
            if(!$error) {
                $user = new User();
                $user->setUsername( $username );
                $user->setRoles( 'ROLE_USER' );

                $factory = $this->get( 'security.encoder_factory' );
                $encoder = $factory->getEncoder( $user );
                $encodedPassword = $encoder->encodePassword( $password, $user->getSalt() );

                $user->setPassword( $encodedPassword );

                $user->setFirstname($first_name);
                $user->setLastname($last_name);

                $em->persist( $user );
                $em->flush();

                return $this->render('DIATestBundle:Welcome:registerSuccess.html.twig');
            }
        }
        return $this->render('DIATestBundle:Welcome:register.html.twig', $response);
    }
}
