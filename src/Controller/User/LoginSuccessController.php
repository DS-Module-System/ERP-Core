<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginSuccessController extends AbstractController
{
    #[Route('login/success', name: 'login_success')]
    public function index(): Response
    {
        return $this->redirect($this->generateUrl('dashboard'));
        $roles = $this->getUser()->getRoles();
        if(in_array("ROLE_ADMIN", $roles) || in_array("ROLE_SUPER_ADMIN", $roles)) {
            return $this->redirect($this->generateUrl('dashboard'));
        } elseif(in_array("ROLE_USER", $roles)) {
            return $this->redirect($this->generateUrl('app_account'));
        }

        die('2');
    }
}
