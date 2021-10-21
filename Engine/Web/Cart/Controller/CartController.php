<?php

namespace ExWife\Engine\Web\Cart\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\WebControllerTrait;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;

use ExWife\Engine\Web\Cart\Form\CheckoutPaymentForm;
use ExWife\Engine\Web\Cart\Form\CheckoutShippingForm;
use ExWife\Engine\Web\Cart\Service\CartService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;


class CartController extends BaseController
{
    use WebControllerTrait;

    /**
     * @var CartService
     */
    protected $_cartService;

    /**
     * CartController constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     * @param Environment $environment
     * @param Security $security
     * @param SessionInterface $session
     * @param CartService $cartService
     */
    public function __construct(Connection $connection, KernelInterface $kernel, Environment $environment, Security $security, SessionInterface $session, CartService $cartService)
    {
        parent::__construct($connection, $kernel, $environment, $security, $session);

        $this->_cartService = $cartService;

    }

    /**
     * @route("/cart")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayCart(Request $request)
    {
        $cart = $this->_cartService->getCart();

        $params = $this->getParamsByUrl('/cart');
        $params['cart'] = $cart;
        return $this->render('cart/cart.twig', $params);
    }

    /**
     * @route("/checkout")
     * @param Request $request
     * @return RedirectResponse
     */
    public function checkout(Request $request)
    {
//        $cart = $this->_cartService->getCart();
//        if ($cart->category == $this->_cartService->getStatusNew()) {
//            $cart->setCategory($this->_cartService->getStatusCreated());
//            $cart->setSubmitted(1);
//            $cart->setSubmittedDate(date('Y-m-d H:i:s'));
//            $cart->save();
//        }
//
//        $cart->save();

        return new RedirectResponse("/checkout/account");
    }

    /**
     * @route("/checkout/account")
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RedirectException
     */
    public function setAccountForCart(Request $request)
    {
        return new RedirectResponse("/checkout/shipping");
    }

    /**
     * @route("/checkout/shipping")
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RedirectException
     */
    public function setShippingForCart(Request $request)
    {
        $cart = $this->_cartService->getCart();
        $form = $this->container->get('form.factory')->create(CheckoutShippingForm::class, $cart, [
            'request' => $request,
            'connection' => $this->_connection,
            'cartService' => $this->_cartService,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($cart->category == $this->_cartService->STATUS_NEW) {
                $cart->category = $this->_cartService->STATUS_CREATED;
                $cart->submitted = 1;
                $cart->submittedDate = date('Y-m-d H:i:s');
                $cart->save();
                return new RedirectResponse("/checkout/payment?id={$cart->title}");
            }
        }

        $params = $this->getParamsByUrl('/cart');
        $params['formView'] = $form->createView();
        $params['cart'] = $cart;
        return $this->render('cart/checkout-shipping.twig', $params);
    }

    /**
     * @route("/checkout/payment")
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RedirectException
     */
    public function setPaymentForCart(Request $request)
    {
        $id = $request->get('id');
        $order = $this->_cartService->getOrderById($id);
        if (!$order) {
            throw new RedirectException("/checkout");
        }
        if ($order->category == $this->_cartService->STATUS_ACCEPTED) {
            throw new RedirectException("/checkout");
        }
        if (!count($order->objOrderItems())) {
            throw new RedirectException("/");
        }

//        $order = $this->_cartService->setBooleanValues($order);
        $this->initialiasePaymentGateways($request, $order);

        $form = $this->container->get('form.factory')->create(CheckoutPaymentForm::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->save();

            $gatewayClass = $this->_cartService->getGatewayClass($order->payType);
            if (!$gatewayClass) {
                throw new NotFoundHttpException();
            }
            $redirectUrl = $gatewayClass->retrieveRedirectUrl($request, $order);
            if ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            }
        }

        $params = $this->getParamsByUrl('/cart');
        $params['formView'] = $form->createView();
        $params['order'] = $order;
        $params['gateways'] = $this->_cartService->getGatewayClasses();
        return $this->render('cart/checkout-payment.twig', $params);
    }

    /**
     * @route("/checkout/finalise")
     * @param Request $request
     * @return mixed
     */
    public function finaliseCart(Request $request)
    {
        $order = null;
        $gatewayClasses = $this->_cartService->getGatewayClasses();
        foreach ($gatewayClasses as $gatewayClass) {
            $order = $gatewayClass->getOrder($request);
            if ($order) {
                break;
            }
        }

        if (!$order) {
            return new RedirectResponse('/checkout');
        }

        $gatewayClass = $this->_cartService->getGatewayClass($order->payType);
        return $gatewayClass->finalise($request, $order);
    }

    /**
     * @route("/checkout/accepted")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayCartAccepted(Request $request)
    {
        $id = $request->get('id');
        $order = $this->_cartService->getOrderById($id);
        if (!$order) {
            throw new NotFoundHttpException();
        }

        $params = $this->getParamsByUrl('/cart');
        $params['order'] = $order;
        return $this->render('cart/checkout-confirm.twig', $params);
    }

    /**
     * @route("/checkout/declined")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayCartDeclined(Request $request)
    {
        $id = $request->get('id');
        $order = $this->_cartService->getOrderById($id);
        if (!$order) {
            throw new NotFoundHttpException();
        }

        $params = $this->getParamsByUrl('/cart');
        $params['order'] = $order;
        return $this->render('cart/checkout-declined.twig', $params);
    }

    /**
     * @param $request
     * @param $order
     */
    protected function initialiasePaymentGateways($request, $order)
    {
        $gatewayClasses = $this->_cartService->getGatewayClasses();
        foreach ($gatewayClasses as $idx => $gatewayClass) {
            if ($idx == 0 && !$order->payType) {
                $order->payType = $gatewayClass->getId();
            }
            $gatewayClass->initialise($request, $order);
        }
    }
}
