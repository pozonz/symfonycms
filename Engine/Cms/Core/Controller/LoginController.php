<?php

namespace ExWife\Engine\Cms\Core\Controller;

use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @route("/manage")
 * Class LoginController
 * @package ExWife\Engine\Cms\Core\Controller
 */
class LoginController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @route("/login")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render("/cms/{$this->_theme}/login.twig", [
            '_theme' => $this->_theme,
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
