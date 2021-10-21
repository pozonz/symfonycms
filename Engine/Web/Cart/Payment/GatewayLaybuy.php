<?php

namespace ExWife\Engine\Web\Cart\Payment;

use ExWife\Engine\Cms\Core\Service\UtilsService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GatewayLaybuy extends AbstractGateway
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function getOrder(Request $request)
    {
        $token = $request->get('token');
        $fullClass = UtilsService::getFullClassFromName('Order');
        return $fullClass::getByField($this->connection, 'payToken', $token);
    }

    /**
     * @param Request $request
     * @param $order
     * @return false|mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function retrieveRedirectUrl(Request $request, $order)
    {
        $start = time();
        $query = [
            "amount" => $order->total,
            "currency" => "NZD",
            "returnUrl" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
            "merchantReference" => $order->title,
            "customer" => [
                "firstName" => $order->shippingFirstName,
                "lastName" => $order->shippingLastName,
                "email" => $order->email,
            ]
        ];
        if ($order->shippingPhone) {
            $query['customer']['phone'] = $order->shippingPhone;
        }

        if ($order->isPickup) {
            $query['customer']['firstName'] = $order->pickupFirstName;
            $query['customer']['lastName'] = $order->pickupLastName;
            $query['customer']['email'] = $order->email;
            if ($order->pickupPhone) {
                $query['customer']['phone'] = $order->pickupPhone;
            }
        }

        $url = '/order/create';

        try {
            $client = $this->getClient();
            $response = $client->request('POST', $url, [
                'json' => $query
            ]);
            $result = $response->getBody()->getContents();
            $jsonData = json_decode($result);

        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        $end = time();
        $seconds = $end - $start;
        $this->addToOrderLog(
            $order,
            $this->getId() . ' - ' . __FUNCTION__,
            $url,
            json_encode($query, JSON_PRETTY_PRINT),
            $result,
            1,
            $seconds
        );


        $status = null;
        $token = null;
        $paymentUrl = null;
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->result)) {
            $status = $jsonData->result ?? null;
            $token = $jsonData->token ?? null;
            $paymentUrl = $jsonData->paymentUrl ?? null;
            $status = 'SUCCESS' == $status ? 1 : 0;
        }

        $order->category = $this->cartService->STATUS_GATEWAY_SENT;
        $order->gatewaySent = 1;
        $order->gatewaySentDate = date('Y-m-d H:i:s');
        $order->payToken = $token;
        $order->paySecret = null;
        $order->save();

        return $paymentUrl;
    }

    /**
     * @param Request $request
     * @param $order
     * @return mixed|RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function finalise(Request $request, $order)
    {
        $start = time();
        $query = [
            'json' => [
                'token' => $order->payToken,
                'amount' => $order->total,
                'currency' => 'NZD'
            ]
        ];
        $url = "/order/confirm";

        try {
            $client = $this->getClient();
            $response = $client->request('POST', $url, $query);
            $result = $response->getBody()->getContents();
            $jsonData = json_decode($result);

        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        $end = time();
        $seconds = $end - $start;
        $this->addToOrderLog(
            $order,
            $this->getId() . ' - ' . __FUNCTION__,
            $url,
            json_encode($query, JSON_PRETTY_PRINT),
            $result,
            1,
            $seconds
        );


        $status = null;
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->result)) {
            $status = $jsonData->result ?? null;
            $status = 'SUCCESS' == $status ? 1 : 0;
        }

        return $this->finaliseOrderAndRedirect($order, $status);
    }

    /**
     * @return int
     */
    public function getInstalment()
    {
        return 6;
    }

    /**
     * @return null
     */
    public function getFrequency()
    {
        return 'weekly';
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return new Client([
            'base_uri' => getenv('LAYBUY_ENDPOINT'),
            'auth' => [
                getenv('LAYBUY_USERNAME'),
                getenv('LAYBUY_KEY')
            ]
        ]);
    }
}