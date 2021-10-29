<?php

namespace ExWife\Engine\Cms\_Core\Controller;

use ExWife\Engine\Cms\_Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\_Core\Base\Controller\Traits\ManageControllerTrait;

use ExWife\Engine\Cms\_Core\Model\Form\OrmProfileForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @route("/manage")
 * Class LoginController
 * @package ExWife\Engine\Cms\_Core\Controller
 */
class ProfileController extends BaseController
{
    use ManageControllerTrait;

    /**
     * @route("/profile")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ExWife\Engine\Cms\_Core\SymfonyKernel\RedirectException
     */
    public function profile(Request $request)
    {
        $user = $this->_security->getUser();
        return $this->_orm($request, null, 'User', $user->id, null, [
            'formClass' => OrmProfileForm::class
        ]);
    }
}
