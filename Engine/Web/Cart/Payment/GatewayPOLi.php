<?php

namespace SymfonyCMS\Engine\Web\Cart\Payment;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Ramsey\Uuid\Uuid;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GatewayPOLi extends AbstractGateway
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
        $authorization = base64_encode(getenv('POLI_MERCHANT_CODE') . ':' . getenv('POLI_AUTHENTICATION_CODE'));
        $query = [
            RequestOptions::JSON => [
                "Amount" => $order->total,
                "CurrencyCode" => 'NZD',
                "MerchantReference" => $order->title,
                "MerchantHomepageURL" => $request->getSchemeAndHttpHost(),
                "SuccessURL" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
                "FailureURL" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
                "CancellationURL" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
                "NotificationURL" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
            ],
            'headers' =>  [
                "Content-Type" => "application/json",
                "Authorization" => "Basic " . $authorization
            ],
        ];
        $url = '/api/v2/Transaction/Initiate';

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
        $secret = null;
        $paymentUrl = null;
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->NavigateURL)) {
            $parts = parse_url($jsonData->NavigateURL);
            parse_str($parts['query'], $query);

            $token = $query['Token'];
            $secret = $jsonData->TransactionRefNo;
            $paymentUrl = $jsonData->NavigateURL;
        }


        $order->category = $this->cartService->STATUS_GATEWAY_SENT;
        $order->gatewaySent = 1;
        $order->gatewaySentDate = date('Y-m-d H:i:s');
        $order->payToken = $token;
        $order->paySecret = $secret;
        $order->save();

        return $paymentUrl;
    }

    /**
     * @param $order
     * @return RedirectResponse
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function finalise(Request $request, $order)
    {
        $start = time();
        $authorization = base64_encode(getenv('POLI_MERCHANT_CODE') . ':' . getenv('POLI_AUTHENTICATION_CODE'));
        $query = [
            'query' => [
                "token" => $order->payToken,
            ],
            'headers' => [
                "Content-Type" => "application/json",
                "Authorization" => "Basic " . $authorization
            ],
        ];
        $url = '/api/v2/Transaction/GetTransaction';

        try {
            $client = $this->getClient();
            $response = $client->request('GET', $url, $query);
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
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->TransactionStatusCode)) {
            $status = $jsonData->TransactionStatusCode === 'Completed' ? 1 : 0;
        }

        return $this->finaliseOrderAndRedirect($order, $status);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return new Client([
            'base_uri' => getenv('POLI_ENDPOINT'),
        ]);
    }
}