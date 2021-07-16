<?php

namespace App\Controller;

use App\Entity\Chatroom;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login/{name?}", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, ?string $name = null): Response
    {
        if (empty($name) || $name === 'not_found') {
            throw $this->createNotFoundException();
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', ['error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        // None
    }
}
