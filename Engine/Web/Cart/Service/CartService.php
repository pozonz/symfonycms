<?php

namespace ExWife\Engine\Web\Cart\Service;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Service\UtilsService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class CartService
{
    public $STATUS_NEW = 0;
    public $STATUS_CREATED = 10;
    public $STATUS_GATEWAY_SENT = 20;
    public $STATUS_ACCEPTED = 30;
    public $STATUS_DECLINED = 40;

    const SESSION_ID = '__order_container_id';

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var SessionInterface
     */
    protected $_session;

    /**
     * @var TokenStorageInterface
     */
    protected $_tokenStorage;

    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * @var \Swift_Mailer
     */
    protected $_mailer;

    /**
     * CartService constructor.
     * @param Connection $container
     */
    public function __construct(Connection $connection, SessionInterface $_session, TokenStorageInterface $tokenStorage, Environment $environment, \Swift_Mailer $mailer)
    {
        $this->_connection = $connection;
        $this->_session = $_session;
        $this->_tokenStorage = $tokenStorage;
        $this->_environment = $environment;
        $this->_mailer = $mailer;
    }

    /**
     * @return string|\Stringable|\Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getCustomer()
    {
        $token = $this->_tokenStorage->getToken();
        if ($token) {
            $customer = $token->getUser();
            if (gettype($customer) == 'object') {
                return $customer;
            }
        }
        return null;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOrderById($id)
    {
        $orderTitle = $id;
        $fullClass = UtilsService::getFullClassFromName('Order');
        return $fullClass::getByField($this->_connection, 'title', $orderTitle);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCart()
    {
        $fullClass = UtilsService::getFullClassFromName('Order');
        $orderId = $this->_session->get(static::SESSION_ID);
        $cart = $fullClass::getById($this->_connection, $orderId);

        if (!$cart) {

            //created a new order only
            $cart = new $fullClass($this->_connection);
            $cart->save();

            //reset the order id
            $cart->title = UtilsService::generateHex(4) . '-' . $cart->id;
            $cart->save();

        } else if ($cart->category != $this->STATUS_NEW) {

            $oldOrder = clone $cart;

            //created a new order and copy the current items over
            $cart->id = null;
            $cart->_uniqId = Uuid::uuid4()->toString();
            $cart->_added = date('Y-m-d H:i:s');
            $cart->_modified = date('Y-m-d H:i:s');
            $cart->submitted = null;
            $cart->submittedDate = null;
            $cart->payStatus = null;
            $cart->payToken = null;
            $cart->paySecret = null;
            $cart->payType = null;
            $cart->emailContent = null;
            $cart->hummRequestQuery = null;
            $cart->logs = null;
            $cart->category = $this->STATUS_NEW;
            $cart->save();

            //reset the order id
            $cart->title = UtilsService::generateHex(4) . '-' . $cart->id;
            $cart->save();

            $this->copyOrderItems($cart, $oldOrder);
        }

        $this->_session->set(static::SESSION_ID, $cart->id);

        //convert 1/0 to boolean
        $cart = $this->setBooleanValues($cart);

//        $customer = static::getCustomer();
//        if ($customer) {
//            $cart->setCustomerId($customer->id);
//            $cart->setCustomerName($customer->getFirstName() . ' ' . $customer->getLastName());
//
//            //if empty, fill customer's info as default
//            $cart->setShippingFirstName($cart->getShippingFirstName() ?: $customer->getFirstName());
//            $cart->setShippingLastName($cart->getShippingLastName() ?: $customer->getLastName());
//            $cart->setEmail($cart->email && filter_var($cart->email, FILTER_VALIDATE_EMAIL) ? $cart->email : $customer->getTitle());
//        }

        if (getenv('SHIPPING_PICKUP_ALLOWED') != 1) {
            $cart->isPickup = 2;
        }

        $this->updateCart($cart);

        //you know...
        return $cart;
    }

    /**
     * @param $newOrder
     * @param $oldOrder
     */
    public function copyOrderItems($newOrder, $oldOrder)
    {
        foreach ($oldOrder->objOrderItems() as $oi) {
            $oi->id = null;
            $oi->_uniqId = Uuid::uuid4()->toString();
            $oi->_added = date('Y-m-d H:i:s');
            $oi->_modified = date('Y-m-d H:i:s');
            $oi->orderId = $newOrder->id;
            $oi->save();
        }
    }

    /**
     * @param $cart
     * @return mixed
     */
    public function setBooleanValues($cart)
    {
        $cart->billingSame = $cart->billingSame ? true : false;
        $cart->billingSave = $cart->billingSave ? true : false;
        $cart->shippingSave = $cart->shippingSave ? true : false;
        $cart->createAnAccount= $cart->createAnAccount ? true : false;
        return $cart;
    }

    /**
     * @param $order
     * @return bool
     */
    public function updateCart($cart)
    {
        $customer = $this->getCustomer();
        $fullClass = UtilsService::getFullClassFromName('PromoCode');
        $promoCode = $fullClass::getActiveByField($this->_connection, 'code', $cart->promoCode);
        if ($promoCode && $promoCode->isValid()) {
            $cart->discountType = $promoCode->type;
            $cart->discountValue = $promoCode->value;
            $cart->promoId = $promoCode->id;
        } else {
            $cart->discountType = null;
            $cart->discountValue = null;
            $cart->promoId = null;
        }

        $subtotal = 0;
        $weight = 0;
        $discount = 0;
        $afterDiscount = 0;
        $totalSaving = 0;

        $cartItems = $cart->objOrderItems();
        foreach ($cartItems as $idx => $itm) {
            $result = $this->updateCartItem($cart, $itm, $customer);
            if ($result) {
                $cartItemSubtotal = $itm->price * $itm->quantity;
                $cartItemWeight = $itm->weight * $itm->quantity;

                $subtotal += $cartItemSubtotal;
                if ($cart->discountType == 2 && !$itm->objVariant()->objProduct()->noPromoDiscount) {
                    $discount += round($cartItemSubtotal * ($cart->discountValue / 100), 2);
                }

                $weight += $cartItemWeight;
                if ($itm->compareAtPrice) {
                    $totalSaving += ($itm->compareAtPrice - $itm->price) * $itm->quantity;
                }
            }
        }
        $cart->orderitems = null;
        $cart->totalSaving = $totalSaving;

        if ($cart->discountType == 1) {
            $discount = min($subtotal, $cart->discountValue);
        }

        $afterDiscount = $subtotal - $discount;

        if ($cart->isPickup == 2) {

            $data = $this->getDeliveryOptions($cart);
            $data = array_filter(array_map(function ($itm) {
                return isset($itm['deliveryOption']) && $itm['deliveryOption'] ? $itm['deliveryOption']->id : null;
            }, array_filter($data, function ($itm) {
                return $itm['valid'] === 1 ? 1 : 0;
            })));

            if (!in_array($cart->shippingId, $data)) {
                if (count($data) > 0) {
                    $cart->shippingId = $data[0];
                } else {
                    $cart->shippingId = null;
                }
            }

            if ($cart->shippingId) {
                $fullClass = UtilsService::getFullClassFromName('ShippingByWeight');
                $deliveryOption = $fullClass::getById($this->_connection, $cart->shippingId);

                $cart->shippingTitle = $deliveryOption->title;
                $cart->shippingCost = $this->getDeliveryFee($cart, $deliveryOption);
            } else {
                $cart->shippingTitle = null;
                $cart->shippingCost = null;
            }

        } else {
            $cart->shippingId = null;
            $cart->shippingTitle = null;
            $cart->shippingCost = null;
        }

        $deliveryFee = $cart->shippingCost ?: 0;
        $total = $afterDiscount + $deliveryFee;
        $gst = ($total * 3) / 23;

        $cart->weight = $weight;
        $cart->subtotal = $subtotal;
        $cart->discount = $discount;
        $cart->afterDiscount = $afterDiscount;
        $cart->tax = $gst;
        $cart->shippingCost = $deliveryFee;
        $cart->total = $total;
        $cart->save();
        return true;
    }

    /**
     * @param $cart
     * @param $cartItem
     * @param $customer
     * @return bool
     */
    public function updateCartItem($cart, $cartItem, $customer)
    {
        if ($cartItem->quantity <= 0) {
            $cartItem->delete();
            return false;
        }

        $variant = $cartItem->objVariant();
        if (!$variant || !$variant->_status) {
            $cartItem->delete();
            return false;
        }

        $product = $variant->objProduct();
        if (!$product || !$product->_status) {
            $cartItem->delete();
            return false;
        }

        if ($variant->stockEnabled) {
            $cartItem->quantity = min($cartItem->quantity, $variant->stock);
        }

        $cartItem->imageUrl = $product->objImage();
        $cartItem->productPageUrl = $product->objProductPageUrl();
        $cartItem->weight = $variant->shippingUnits ?: 0;

        if ($product->objOnSaleActive() && $variant->salePrice) {
            $cartItem->price = $variant->calculatedSalePrice($customer);
            $cartItem->compareAtPrice = $variant->calculatedPrice($customer);
        } else {
            $cartItem->price = $variant->calculatedPrice($customer);
        }


        $discountType = $cart->discountType;
        $discountValue = $cart->discountValue;

        if ($discountType == 1 && !$product->noPromoDiscount) {
            $cartItem->compareAtPrice = $cartItem->compareAtPrice ?: $cartItem->price;
            $afterDiscount = $cartItem->price * (100 - $discountValue) / 100;
            $discountedTotal = $cartItem->price - $afterDiscount;
            $cartItem->price = $afterDiscount;
        }

        $cartItem->save();
        return true;
    }

    /**
     * @param $cart
     * @return array
     */
    public function getDeliveryOptions($cart)
    {
        $country = $cart->shippingCountry;
        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
        $ormCountry = $fullClass::getByField($this->_connection, 'code', $country);
        if (!$ormCountry) {
            return [];
        }

        $deliveryOptions = [];
        $fullClass = UtilsService::getFullClassFromName('ShippingByWeight');
        $data = $fullClass::active($this->_connection, [
            'whereSql' => 'm.country = ?',
            'params' => [$ormCountry->id],
        ]);

        if (getenv('SHIPPING_PRICE_MODE') == 1) {
            $region = $cart->shippingState;
            $ormRegion = $fullClass::getByField($this->_connection, 'title', $region);
            $data = array_filter($data, function ($itm) use ($ormRegion) {
                if (!$ormRegion) {
                    return 1;
                }
                $objShippingCostRates = $itm->objShippingCostRates();
                foreach ($objShippingCostRates as $objShippingCostRate) {
                    if (in_array($ormRegion->id, $objShippingCostRate->regions) || in_array('all', $objShippingCostRate->regions)) {
                        return 1;
                    }
                }
                return 0;
            });

        } else if (getenv('SHIPPING_PRICE_MODE') == 2) {
            $postcode = $cart->shippingPostcode;

            $data = array_filter($data, function ($itm) use ($postcode) {
                if (!$postcode) {
                    return 1;
                }
                $objShippingCostRates = $itm->objShippingCostRates();
                foreach ($objShippingCostRates as $objShippingCostRate) {
                    if ($objShippingCostRate->zipFrom && $objShippingCostRate->zipFrom > $postcode) {
                        continue;
                    }
                    if ($objShippingCostRate->zipTo && $objShippingCostRate->zipTo < $postcode) {
                        continue;
                    }
                    return 1;
                }
                return 0;
            });

        } else {
            $data = [];
        }

        foreach ($data as $itm) {
            $deliveryFee = $this->getDeliveryFee($cart, $itm);
            $deliveryOptions[] = [
                'deliveryOption' => $itm,
                'valid' => $deliveryFee === null ? 0 : 1,
                'fee' => $deliveryFee,
            ];
        }
        return $deliveryOptions;
    }

    /**
     * @param $deliveryOption
     * @return int
     */
    public function getDeliveryFee($cart, $deliveryOption)
    {
        $country = $cart->shippingCountry;
        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
        $ormCountry = $fullClass::getByField($this->_connection, 'code', $country);
        if (!$ormCountry) {
            return null;
        }

        if ($deliveryOption->country !== $ormCountry->id) {
            return null;
        }

        if (getenv('SHIPPING_PRICE_MODE') == 1) {
            $region = $cart->shippingState;
            $ormRegion = $fullClass::getByField($this->_connection, 'title', $region);

            if (!$ormRegion) {
                return null;
            }

            $objShippingCostRates = $deliveryOption->objShippingCostRates();
            foreach ($objShippingCostRates as $objShippingCostRate) {

                if (in_array($ormRegion->id, $objShippingCostRate->regions) || in_array('all', $objShippingCostRate->regions)) {

                    $weight = $cart->weight;

                    foreach ($objShippingCostRate->extra as $itm) {
                        $from = $itm->from ?: 0;
                        $to = $itm->to ?: 0;

                        if ($weight >= $from && $weight <= $to) {
                            return $itm->price;
                        }
                    }

                    return $objShippingCostRate->price * $weight;
                }
            }

        } else if (getenv('SHIPPING_PRICE_MODE') == 2) {
            $postcode = $cart->shippingPostcode;

            $objShippingCostRates = $deliveryOption->objShippingCostRates();
            foreach ($objShippingCostRates as $objShippingCostRate) {
                if ($objShippingCostRate->zipFrom && $objShippingCostRate->zipFrom > $postcode) {
                    continue;
                }
                if ($objShippingCostRate->zipTo && $objShippingCostRate->zipTo < $postcode) {
                    continue;
                }

                $weight = $cart->weight;

                foreach ($objShippingCostRate->extra as $itm) {
                    $from = $itm->from ?: 0;
                    $to = $itm->to ?: 0;

                    if ($weight >= $from && $weight <= $to) {
                        return $itm->price;
                    }
                }

                return $objShippingCostRate->price * $weight;
            }
        }


        return null;
    }

    /**
     * @return array
     */
    public function getDeliverableCountries()
    {
        $fullClass = UtilsService::getFullClassFromName('ShippingByWeight');
        $data = $fullClass::active($this->_connection);
        return array_filter(array_map(function ($itm) {
            return $itm->objCountry();
        }, $data));
    }

    /**
     * @param $cart
     * @return array
     */
    public function getDeliverableRegions($cart)
    {
        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
        $orm = $fullClass::getByField($this->_connection, 'code', $cart->shippingCountry);
        if (!$orm) {
            throw new NotFoundHttpException();
        }

        $fullClass = UtilsService::getFullClassFromName('ShippingByWeight');
        $data = $fullClass::active($this->_connection);
        $data = array_filter($data, function ($itm) use ($orm) {
            return $itm->country == $orm->id ? 1 : 0;
        });

        $regions = [];
        foreach ($data as $itm) {
            $objShippingCostRates = $itm->objShippingCostRates();
            foreach ($objShippingCostRates as $objShippingCostRate) {
                foreach ($objShippingCostRate->regions as $region) {
                    if ($region === 'all') {
                        $regions = array_merge($regions, array_map(function ($itm) use ($orm) {
                            return $itm->title;
                        }, $orm->objChildren()));
                    } else {
                        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
                        $r = $fullClass::getById($this->_connection, $region);
                        $regions[] = $r ? $r->title : null;
                    }
                }
            }
        }

        $regions = array_filter(array_unique($regions));
        sort($regions);
        return $regions;
    }

    /**
     *
     */
    public function clearCart()
    {
        $this->_session->set(static::SESSION_ID, null);
    }

    /**
     * @param $order
     * @return mixed
     */
    public function sendEmailInvoice($order)
    {
        $messageBody = $this->_environment->render('cart/email-invoice.twig', array(
            'order' => $order,
        ));
        $message = (new \Swift_Message())
            ->setSubject((getenv('EMAIL_ORDER_SUBJECT') ?: 'Your order has been received') . " - #{$order->title}")
            ->setFrom([
                getenv('EMAIL_FROM') => getenv('EMAIL_FROM_NAME')
            ])
            ->setTo([$order->email])
            ->setBcc(array_filter(explode(',', getenv('EMAIL_BCC_ORDER'))))
            ->setBody(
                $messageBody, 'text/html'
            );
        return $this->_mailer->send($message);
    }

    /**
     * @param $order
     */
    public function updateStock($order)
    {
        foreach ($order->objOrderItems() as $orderItem) {
            $variant = $orderItem->objVariant();
            if ($variant) {
                $variant->stock = $variant->stock - $orderItem->quantity;
                $variant->save();
            }
        }
    }

    /**
     * @return array
     */
    public function getGatewayClasses()
    {
        $gatewayClasses = [];

        $paymentMethods = explode(',', getenv('PAYMENT_METHODS'));
        foreach ($paymentMethods as $paymentMethod) {
            $gatewayClasses[] = $this->getGatewayClass($paymentMethod);
        }
        return array_filter($gatewayClasses);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getGatewayClass($code)
    {
        $nameSpaces = [
            '\\App\\Cart\\Payment\\',
            '\\ExWife\\Engine\\Web\\Cart\\Payment\\',
        ];
        foreach ($nameSpaces as $nameSpace) {
            $class = "{$nameSpace}Gateway{$code}";
            if (class_exists($class)) {
                return new $class($this->_connection, $this);
            }
        }
        return null;
    }

    /**
     * @param $type
     * @param $url
     * @param $request
     * @param $response
     * @param $status
     * @param string $seconds
     * @return \stdClass
     */
    public function getLogBlock($type, $url, $request, $response, $status, $seconds = '')
    {
        $block = new \stdClass();
        $block->id = Uuid::uuid4()->toString();
        $block->title = 'Log';
        $block->status = 1;
        $block->block = "5";
        $block->twig = "_";
        $block->values = new \stdClass();
        $block->values->type = $type;
        $block->values->status = $status;
        $block->values->url = $url;
        $block->values->date = date('d M Y H:i:s');
        $block->values->secondsUsed = $seconds;
        $block->values->request = $request;
        $block->values->response = $response;
        return $block;
    }

    /**
     * @param $sections
     * @return \stdClass[]
     */
    public function getLogBlankSections($sections)
    {
        $section = new \stdClass();
        $section->id = Uuid::uuid4()->toString();
        $section->title = 'Logs';
        $section->attr = 'logs';
        $section->status = 1;
        $section->tags = ["1"];
        $section->blocks = [];
        return [$section];
    }
}