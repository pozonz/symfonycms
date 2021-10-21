<?php

namespace ExWife\Engine\Web\Cart\Payment;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractGateway
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * PaymentInterface constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection, $cartService)
    {
        $this->connection = $connection;
        $this->cartService = $cartService;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        $rc = static::getReflectionClass();
        return str_replace('Gateway', '', $rc->getShortName());
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return 'Pay by ' . $this->getId();
    }

    /**
     * @return mixed
     */
    abstract public function getOrder(Request $request);

    /**
     * @return mixed
     */
    public function initialise(Request $request, $order)
    {

    }

    /**
     * @param Request $request
     * @param $order
     * @return false
     */
    public function retrieveRedirectUrl(Request $request, $order)
    {
        return false;
    }

    /**
     * @param $order
     * @return mixed
     */
    abstract public function finalise(Request $request, $order);

    /**
     * @return int
     */
    public function getInstalment()
    {
        return 1;
    }

    /**
     * @return null
     */
    public function getFrequency()
    {
        return null;
    }

    /**
     * @param $order
     * @param $type
     * @param $url
     * @param $request
     * @param $response
     * @param $status
     * @param $seconds
     */
    protected function addToOrderLog($order, $type, $url, $request, $response, $status, $seconds)
    {
        $sections = json_decode($order->logs ?: '[]');
        if (!count($sections)) {
            $sections = $this->cartService->getLogBlankSections($sections);
        }
        $sections[0]->blocks[] = $this->cartService->getLogBlock(
            $type,
            $url,
            $request,
            $response,
            $status,
            $seconds
        );

        $order->logs = json_encode($sections);
        $order->save();
    }

    /**
     * @param $order
     * @param $status
     * @return RedirectResponse
     */
    protected function finaliseOrderAndRedirect($order, $status)
    {
        if ($status == 1) {
            if ($order->category != $this->cartService->STATUS_ACCEPTED) {
                $order->payStatus = 1;
                $order->category = $this->cartService->STATUS_ACCEPTED;
                $order->save();

                $this->cartService->sendEmailInvoice($order);
                $this->cartService->updateStock($order);
                $this->cartService->clearCart();
            }

            return new RedirectResponse('/checkout/accepted?id=' . $order->title);

        } else {

            $order->payStatus = 0;
            $order->category = $this->cartService->STATUS_DECLINED;
            $order->save();
            return new RedirectResponse('/checkout/declined?id=' . $order->title);
            
        }
    }

    /**
     * @return \ReflectionClass
     */
    static public function getReflectionClass()
    {
        return new \ReflectionClass(get_called_class());
    }
}