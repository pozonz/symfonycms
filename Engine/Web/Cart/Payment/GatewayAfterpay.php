<?php

namespace ExWife\Engine\Web\Cart\Payment;

use ExWife\Engine\Cms\Core\Service\UtilsService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Ramsey\Uuid\Uuid;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GatewayAfterpay extends AbstractGateway
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function getOrder(Request $request)
    {
        $token = $request->get('orderToken');
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
        $authorization = base64_encode(getenv('AFTERPAY_MID') . ':' . getenv('AFTERPAY_MKEY'));
        $query = [
            RequestOptions::JSON => [
                "totalAmount" => [
                    "amount" => $order->total,
                    "currency" => 'NZD'
                ],
                "consumer" => [
                    "phoneNumber" => $order->shippingPhone,
                    "givenNames" => $order->shippingFirstName,
                    "surname" => $order->shippingLastName,
                    "email" => $order->email,
                ],
                "shipping" => [
                    "name" => substr($order->shippingFirstName . ' ' . $order->shippingLastName, 0, 255),
                    "line1" => substr($order->shippingAddress, 0, 128),
                    "line2" => substr($order->shippingAddress2, 0, 128),
                    "state" => substr($order->shippingCity, 0, 128),
                    "postcode" => substr($order->shippingPostcode, 0, 128),
                    "countryCode" => substr($order->shippingCountry, 0, 128),
                    "phoneNumber" => substr($order->shippingPhone, 0, 128),
                ],
                "billing" => [
                    "name" => substr($order->billingSame ? $order->shippingFirstName . ' ' . $order->shippingLastName : $order->shippingFirstName . ' ' . $order->shippingLastName, 0, 255),
                    "line1" => substr($order->billingSame ? $order->shippingAddress : $order->shippingAddress, 0, 128),
                    "line2" => substr($order->billingSame ? $order->shippingAddress2 : $order->shippingAddress2, 0, 128),
                    "state" => substr($order->billingSame ? $order->shippingCity : $order->shippingCity, 0, 128),
                    "postcode" => substr($order->billingSame ? $order->shippingPostcode : $order->shippingPostcode, 0, 128),
                    "countryCode" => substr($order->billingSame ? $order->shippingCountry : $order->shippingPhone, 0, 128),
                    "phoneNumber" => substr($order->billingSame ? $order->shippingPhone : $order->shippingPhone, 0, 128),
                ],
                "merchant" => [
                    "redirectConfirmUrl" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
                    "redirectCancelUrl" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
                ],
                "merchantReference" => $order->title,
            ],
            'headers' => [
                "User-Agent" => "MyAfterpayModule/1.0.0 (Custom E-Commerce Platform/1.0.0; PHP/7.3; Merchant/" . getenv('AFTERPAY_MID') . ') ' . $request->getSchemeAndHttpHost(),
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Basic " . $authorization
            ],
        ];
        $url = '/v1/orders';

        try {
            $client = $this->getClient();
            $response = $client->request('POST', $url, $query);
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

        $token = null;
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->token)) {
            $token = $jsonData->token;
        }

        $order->category = $this->cartService->STATUS_GATEWAY_SENT;
        $order->gatewaySent = 1;
        $order->gatewaySentDate = date('Y-m-d H:i:s');
        $order->payToken = $token;
        $order->paySecret = null;
        $order->save();

        return null;
    }

    /**
     * @param $order
     * @return RedirectResponse
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function finalise(Request $request, $order)
    {
        $start = time();
        $authorization = base64_encode(getenv('AFTERPAY_MID') . ':' . getenv('AFTERPAY_MKEY'));
        $query = [
            RequestOptions::JSON => [
                "token" => $order->payToken,
            ],
            'headers' => [
                "User-Agent" => "MyAfterpayModule/1.0.0 (Custom E-Commerce Platform/1.0.0; PHP/7.3; Merchant/" . getenv('AFTERPAY_MID') . ') ' . $request->getSchemeAndHttpHost(),
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Basic " . $authorization
            ],
        ];
        $url = '/v1/payments/capture';

        try {
            $client = $this->getClient();
            $response = $client->request('POST', $url, $query);
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
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->status)) {
            $status = $jsonData->status === 'APPROVED' ? 1 : 0;
        }

        return $this->finaliseOrderAndRedirect($order, $status);
    }

    /**
     * @return int
     */
    public function getInstalment()
    {
        return 4;
    }

    /**
     * @return null
     */
    public function getFrequency()
    {
        return 'fortnightly';
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return new Client([
            'base_uri' => getenv('AFTERPAY_ENDPOINT'),
        ]);
    }
}