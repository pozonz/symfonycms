<?php

namespace ExWife\Engine\Web\Cart\Controller;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\ManageControllerTrait;
use ExWife\Engine\Cms\Core\Base\Controller\Traits\WebControllerTrait;
use ExWife\Engine\Cms\Core\Service\CmsService;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use ExWife\Engine\Cms\File\Service\FileManagerService;

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


class CartApiController extends AbstractController
{
    protected $outOfStockMessage = "Sorry, you are trying to order <strong>{{qty}} x</strong> \"{{productName}}\"{{extra}} but we have only {{stock}} left in total.";

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var KernelInterface
     */
    protected $_kernel;

    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * @var CmsService
     */
    protected $_cmsService;

    /**
     * @var Security
     */
    protected $_security;

    /**
     * @var SessionInterface
     */
    protected $_session;

    /**
     * @var CartService 
     */
    protected $_cartService;

    /**
     * CartApiController constructor.
     * @param Connection $connection
     * @param KernelInterface $kernel
     * @param Environment $environment
     * @param Security $security
     * @param SessionInterface $session
     * @param CartService $cartService
     */
    public function __construct(Connection $connection, KernelInterface $kernel, Environment $environment, Security $security, SessionInterface $session, CartService $cartService)
    {
        $this->_connection = $connection;
        $this->_kernel = $kernel;
        $this->_environment = $environment;
        $this->_security = $security;
        $this->_session = $session;
        $this->_cartService = $cartService;
        $dir = __DIR__ . '/../../../../Resources/views/web';
        if (file_exists($dir)) {
            $this->_environment->getLoader()->addPath($dir);
        }
    }

    /**
     * @Route("/cart/get")
     * @param Request $request
     * @param Environment $environment
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getCart(Request $request)
    {
        $cart = $this->_cartService->getCart();

        return new JsonResponse([
            'cart' => $cart,
            'miniCartHtml' => $this->_environment->render('cart/cart-mini.twig', [
                'cart' => $cart,
            ]),
        ]);
    }

    /**
     * @Route("/cart/post/cart-item/add")
     * @param Request $request
     * @param Environment $environment
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function addToCart(Request $request)
    {
        $id = $request->get('id');
        $qty = $request->get('qty');
        $isOutOfStock = 0;
        $outOfStockMessage = $this->outOfStockMessage ?? '';

        $cart = $this->_cartService->getCart();

        $productVariantFullClass = UtilsService::getFullClassFromName('ProductVariant');
        $cartItemFullClass = UtilsService::getFullClassFromName('OrderItem');

        $variant = $productVariantFullClass::getById($this->_connection, $id);
        if (!$variant || !$variant->_status) {
            throw new NotFoundHttpException('Variant not found');
        }

        $product = $variant->objProduct();
        if (!$product || !$product->_status) {
            throw new NotFoundHttpException('Product not found');
        }

        $exist = false;
        $cartItems = $cart->objOrderItems();
        foreach ($cartItems as $itm) {
            if ($itm->productId == $variant->id) {
                $exist = true;

                if (!$variant->stockEnabled || $variant->stock >= ($itm->quantity + $qty)) {
                    $itm->quantity = $itm->quantity + $qty;
                    $itm->save();
                } else {
                    $isOutOfStock = 1;
                    $outOfStockMessage = str_replace('{{productName}}', $product->title, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{variantName}}', $variant->title, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{stock}}', $variant->stock, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{qty}}', $itm->quantity + $qty, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{extra}}', " ({$itm->quantity} item" . ($itm->quantity == 1 ? ' is' : 's are') . " already in the cart)", $outOfStockMessage);
                }
            }
        }

        if (!$exist) {
            if (!$variant->stockEnabled || $variant->stock >= $qty) {
                /** @var OrderItem $cartItem */
                $cartItem = new $cartItemFullClass($this->_connection);
                $cartItem->title = $product->title . ($variant->title ? ' - ' . $variant->title : '');
                $cartItem->productName = $product->title;
                $cartItem->variantName = $variant->title;
                if ($product->objBrand()) {
                    $cartItem->brandName = $product->objBrand()->title;
                }
                $cartItem->sku = $variant->sku;
                $cartItem->orderId = $cart->id;
                $cartItem->productId = $variant->id;
                $cartItem->quantity = $qty;
                $cartItem->image = $product->thumbnail;
                $cartItem->save();
            } else {
                $isOutOfStock = 1;
                $outOfStockMessage = str_replace('{{productName}}', $product->title, $outOfStockMessage);
                $outOfStockMessage = str_replace('{{variantName}}', $variant->title, $outOfStockMessage);
                $outOfStockMessage = str_replace('{{stock}}', $variant->stock, $outOfStockMessage);
                $outOfStockMessage = str_replace('{{qty}}', $qty, $outOfStockMessage);
                $outOfStockMessage = str_replace('{{extra}}', "", $outOfStockMessage);
            }
        }

        $fullClass = UtilsService::getFullClassFromName('Order');
        $cart = $fullClass::getById($this->_connection, $cart->id);
        $this->_cartService->updateCart($cart);

        return new JsonResponse([
            'isOutOfStock' => $isOutOfStock,
            'outOfStockMessage' => $outOfStockMessage,
            'cart' => $cart,
            'miniCartHtml' => $this->_environment->render('cart/cart-mini.twig', [
                'cart' => $cart,
            ]),
        ]);
    }

    /**
     * @Route("/cart/post/cart-item/delete")
     * @param Request $request
     * @param Environment $environment
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function deleteOrderItem(Request $request)
    {
        $id = $request->get('id');

        $cart = $this->_cartService->getCart();

        $cartItems = $cart->objOrderItems();
        foreach ($cartItems as $itm) {
            if ($itm->id == $id) {
                $itm->delete();
            }
        }

        $fullClass = UtilsService::getFullClassFromName('Order');
        $cart = $fullClass::getById($this->_connection, $cart->id);
        $this->_cartService->updateCart($cart);

        return new JsonResponse([
            'cart' => $cart,
            'miniCartHtml' => $this->_environment->render('cart/cart-mini.twig', [
                'cart' => $cart,
            ]),
            'miniCartSubtotalHtml' => $this->_environment->render('cart/includes/cart-mini-subtotal.twig', [
                'cart' => $cart,
            ]),
            'cartSubtotalHtml' => $this->_environment->render('cart/includes/cart-subtotal.twig', [
                'cart' => $cart,
            ]),
        ]);
    }

    /**
     * @Route("/cart/post/cart-item/qty")
     * @param Request $request
     * @param Environment $environment
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function changeOrderItemQty(Request $request)
    {
        $id = $request->get('id');
        $qty = $request->get('qty');
        $isOutOfStock = 0;
        $outOfStockMessage = $this->outOfStockMessage ?? '';
        $stock = 0;

        $cart = $this->_cartService->getCart();

        $cartItem = null;
        $cartItems = $cart->objOrderItems();
        foreach ($cartItems as $itm) {
            if ($itm->id == $id) {
                $variant = $itm->objVariant();
                if (!$variant || !$variant->_status) {
                    throw new NotFoundHttpException('Variant not found');
                }

                $product = $variant->objProduct();
                if (!$product || !$product->_status) {
                    throw new NotFoundHttpException('Product not found');
                }

                $stock = $variant->stock;

                if (!$variant->stockEnabled || $variant->stock >= $qty) {
                    $itm->quantity = $qty;
                    $itm->save();
                } else {
                    $isOutOfStock = 1;
                    $outOfStockMessage = str_replace('{{productName}}', $product->title, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{variantName}}', $variant->title, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{stock}}', $variant->stock, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{qty}}', $qty, $outOfStockMessage);
                    $outOfStockMessage = str_replace('{{extra}}', "", $outOfStockMessage);
                }

                $cartItem = $itm;
            }
        }

        $fullClass = UtilsService::getFullClassFromName('Order');
        $cart = $fullClass::getById($this->_connection, $cart->id);
        $this->_cartService->updateCart($cart);

        return new JsonResponse([
            'isOutOfStock' => $isOutOfStock,
            'outOfStockMessage' => $outOfStockMessage,
            'stock' => $stock,
            'cart' => $cart,
            'miniCartHtml' => $this->_environment->render('cart/cart-mini.twig', [
                'cart' => $cart,
            ]),
            'miniCartSubtotalHtml' => $this->_environment->render('cart/includes/cart-mini-subtotal.twig', [
                'cart' => $cart,
            ]),
            'cartSubtotalHtml' => $this->_environment->render('cart/includes/cart-subtotal.twig', [
                'cart' => $cart,
            ]),
            'cartItemSubtotalHtml' => $this->_environment->render('cart/includes/cart-item-subtotal.twig', [
                'itm' => $cartItem,
            ]),
        ]);
    }

    /**
     * @Route("/checkout/post/order/promo-code")
     * @param Request $request
     * @param Environment $environment
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function applyPromoCode(Request $request)
    {
        $code = $request->get('code');

        $cart = $this->_cartService->getCart();
        $cart->promoCode = $code;
        $cart->save();

        $cart = $this->_cartService->getCart();

        return new JsonResponse([
            'orderTotalFormatted' => number_format($cart->total, 2),
            'cart' => $cart,
            'checkoutSidebarSubtotalHtml' => $this->_environment->render('cart/includes/checkout-sidebar-subtotal.twig', [
                'cart' => $cart,
            ]),
        ]);
    }

    /**
     * @Route("/checkout/post/order/change-pay-type")
     * @param Request $request
     * @return JsonResponse
     * @throws RedirectException
     */
    public function changeOrderPayType(Request $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');
        $order = $this->_cartService->getOrderById($id);
        if (!$order) {
            throw new NotFoundHttpException();
        }

        $order->payType = $type;
        $order->save();

        return new JsonResponse([
            'order' => $order,
        ]);
    }

    /**
     * @Route("/checkout/post/order/send-to-payment-gateway")
     * @param Request $request
     * @param Environment $environment
     * @return JsonResponse
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendToPaymentGateway(Request $request)
    {
        $type = $request->get('type');
        $note = $request->get('note');
        $id = $request->get('id');
        $order = $this->_cartService->getOrderById($id);
        if (!$order) {
            throw new NotFoundHttpException();
        }

        $order->category = $this->_cartService->STATUS_GATEWAY_SENT;
        $order->gatewaySent = 1;
        $order->gatewaySentDate = date('Y-m-d H:i:s');
        $order->payType = $type;
        $order->note = $note;
        $order->save();

        return new JsonResponse([
            'order' => $order,
            'checkoutSidebarHtml' => $this->_environment->render('cart/includes/checkout-sidebar.twig', [
                'cart' => $order,
            ]),
        ]);
    }

    /**
     * @Route("/checkout/post/order/shipping")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateShippingOptions(Request $request)
    {
        $address = $request->get('address');
        $city = $request->get('city');
        $country = $request->get('country');
        $region = $request->get('region');
        $postcode = $request->get('postcode');
        $pickup = $request->get('pickup');

        $cart = $this->_cartService->getCart();
        $cart->shippingAddress = $address;
        $cart->shippingCity = $city;
        $cart->shippingState = $region;
        $cart->shippingCountry = $country;
        $cart->shippingPostcode = $postcode;
        $cart->isPickup = $pickup;
        $cart->save();

        $this->_cartService->updateCart($cart);

        $regions = $this->_cartService->getDeliverableRegions($cart);
        $deliveryOptions = $this->_cartService->getDeliveryOptions($cart);
        return new JsonResponse([
            'shippingPriceMode' => getenv('SHIPPING_PRICE_MODE') ?? 1,
            'cart' => $cart,
            'regions' => $regions,
            'deliveryOptions' => $deliveryOptions,
            'checkoutSidebarSubtotalHtml' => $this->_environment->render('cart/includes/checkout-sidebar-subtotal.twig', [
                'cart' => $cart,
            ]),
        ]);
    }

    /**
     * @Route("/checkout/post/order/delivery")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDeliveryOption(Request $request)
    {
        $shipping = $request->get('shipping');

        $cart = $this->_cartService->getCart();
        $cart->shippingId = $shipping;
        $cart->save();

        $this->_cartService->updateCart($cart);

        $regions = $this->_cartService->getDeliverableRegions($cart);
        $deliveryOptions = $this->_cartService->getDeliveryOptions($cart);
        return new JsonResponse([
            'regions' => $regions,
            'deliveryOptions' => $deliveryOptions,
            'checkoutSidebarSubtotalHtml' => $this->_environment->render('cart/includes/checkout-sidebar-subtotal.twig', [
                'cart' => $cart,
            ]),
        ]);
    }
}
