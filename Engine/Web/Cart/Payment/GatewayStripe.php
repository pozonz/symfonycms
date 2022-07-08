<?php

namespace SymfonyCMS\Engine\Web\Cart\Payment;

use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GatewayStripe extends AbstractGateway
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function getOrder(Request $request)
    {
        $token = $request->get('id');
        $fullClass = UtilsService::getFullClassFromName('Order');
        return $fullClass::getByField($this->connection, 'payToken', $token);
    }

    /**
     * @param Request $request
     * @param $order
     * @return mixed|void
     */
    public function initialise(Request $request, $order)
    {
        $start = time();

        if (!$order->payToken || !$order->paySecret) {

            $query = [
                'amount' => $order->total * 100,
                'currency' => 'nzd',
            ];
            try {
                Stripe::setApiKey(getenv('STRIPE_SERVER_KEY'));
                $result = PaymentIntent::create($query);
            } catch (\Exception $ex) {
                $result = $ex->getMessage();
            }

            $end = time();
            $seconds = $end - $start;
            $this->addToOrderLog(
                $order,
                $this->getId() . ' - ' . __FUNCTION__,
                '',
                json_encode($query, JSON_PRETTY_PRINT),
                json_encode($result, JSON_PRETTY_PRINT),
                1,
                $seconds
            );

            $order->payToken = $result->id;
            $order->paySecret = $result->client_secret;
            $order->payType = $this->getId();
            $order->save();

        } else {

            $query = [
                'amount' => $order->total * 100,
                'currency' => 'nzd',
            ];
            try {
                Stripe::setApiKey(getenv('STRIPE_SERVER_KEY'));
                $result = PaymentIntent::update($order->payToken, $query);
            } catch (\Exception $ex) {
                $result = $ex->getMessage();
            }

            $end = time();
            $seconds = $end - $start;
            $this->addToOrderLog(
                $order,
                $this->getId() . ' - ' . __FUNCTION__,
                '',
                json_encode($query, JSON_PRETTY_PRINT),
                json_encode($result, JSON_PRETTY_PRINT),
                1,
                $seconds
            );

            $order->payType = $this->getId();
            $order->save();
            
        }
    }

    /**
     * @param Request $request
     * @param $order
     * @return mixed|RedirectResponse
     */
    public function finalise(Request $request, $order)
    {
        $start = time();
        try {
            Stripe::setApiKey(getenv('STRIPE_SERVER_KEY'));
            $result = PaymentIntent::retrieve($order->payToken);
            $status = $result->status == 'succeeded' ? 1 : 0;

        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        $end = time();
        $seconds = $end - $start;
        $this->addToOrderLog(
            $order,
            $this->getId() . ' - ' . __FUNCTION__,
            '',
            $order->payToken,
            json_encode($result, JSON_PRETTY_PRINT),
            $status,
            $seconds
        );

        return $this->finaliseOrderAndRedirect($order, $status);
    }
}