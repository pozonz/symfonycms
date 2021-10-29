<?php

namespace ExWife\Engine\Web\Cart\Payment;

use ExWife\Engine\Cms\_Core\Service\UtilsService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GatewayWindCave extends AbstractGateway
{
    protected $params = [
        'PxPayUserId' => null,
        'PxPayKey' => null,
        'UrlFail' => null,
        'UrlSuccess' => null,
        'AmountInput' => null,
        'EnableAddBillCard' => 1,
        'Opt' => null,
        'TxnType' => 'Purchase',
        'CurrencyInput' => 'NZD',
        'TxnData1' => null,
        'TxnData2' => null,
        'TxnData3' => null,
        'MerchantReference' => null,
        'EmailAddress' => null,
        'BillingId' => null,
        'TxnId' => null,
        'DpsTxnRef' => null,
        'DpsBillingId' => null,
    ];

    /**
     * @param Request $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOrder(Request $request)
    {
        $start = time();

        $token = $request->get('result');
        if (!$token) {
            return null;
        }

        $this->params = array_merge($this->params, [
            'PxPayUserId' => getenv('PX_ACCESS_USERID'),
            'PxPayKey' => getenv('PX_ACCESS_KEY'),
            'Response' => $token,
        ]);
        $xmlRequest = $this->toGatewayXmlRequest('<ProcessResponse/>');

        $query = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8',
            ],
            'body' => $xmlRequest,
        ];
        $url = '/pxaccess/pxpay.aspx';

        try {
            $client = $this->getClient();
            $response = $client->request('POST', $url, $query);
            $result = $response->getBody()->getContents();

            $xmlResponse = new \SimpleXMLElement($result);
            $status = $xmlResponse->Success->__toString();
            $orderTitle = $xmlResponse->MerchantReference->__toString();

        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        $fullClass = UtilsService::getFullClassFromName('Order');
        $order = $fullClass::getByField($this->connection, 'title', $orderTitle);
        if (!$order) {
            return null;
        }

        $end = time();
        $seconds = $end - $start;
        $this->addToOrderLog(
            $order,
            $this->getId() . ' - ' . __FUNCTION__,
            $url,
            json_encode($query, JSON_PRETTY_PRINT),
            $result,
            $status,
            $seconds
        );

        if ($status == 1) {
            $order->payStatus = 1;
            $order->save();
        } else {
            $order->payStatus = 0;
            $order->save();
        }

        return $order;
    }

    /**
     * @param Request $request
     * @param $order
     * @return false
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function retrieveRedirectUrl(Request $request, $order)
    {
        $start = time();

        $this->params = array_merge($this->params, [
            'PxPayUserId' => getenv('PX_ACCESS_USERID'),
            'PxPayKey' => getenv('PX_ACCESS_KEY'),
            'UrlFail' => $request->getSchemeAndHttpHost() . '/checkout/finalise',
            'UrlSuccess' => $request->getSchemeAndHttpHost() . '/checkout/finalise',
            'AmountInput' => number_format($order->total, 2),
            'EmailAddress' => $order->email,
            'BillingId' => "{$order->shippingFirstName} {$order->shippingLastName}",
            'TxnId' => null,
            'MerchantReference' => $order->title,
        ]);
        $xmlRequest = $this->toGatewayXmlRequest();

        $query = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8',
            ],
            'body' => $xmlRequest,
        ];
        $url = '/pxaccess/pxpay.aspx';

        try {
            $client = $this->getClient();
            $response = $client->request('POST', $url, $query);
            $result = $response->getBody()->getContents();

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

        $xmlResponse = new \SimpleXMLElement($result);
        $paymentUrl = $xmlResponse->URI->__toString();

        $order->category = $this->cartService->STATUS_GATEWAY_SENT;
        $order->gatewaySent = 1;
        $order->gatewaySentDate = date('Y-m-d H:i:s');
        $order->payToken = null;
        $order->paySecret = null;
        $order->save();

        return $paymentUrl;
    }

    /**
     * @param Request $request
     * @param $order
     * @return mixed|RedirectResponse
     */
    public function finalise(Request $request, $order)
    {
        return $this->finaliseOrderAndRedirect($order, $order->payStatus == 1 ? 1 : 0);
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return new Client([
            'base_uri' => getenv('PX_ACCESS_URL')
        ]);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function toGatewayXmlRequest($head = '<GenerateRequest/>')
    {
        $xml = new \SimpleXMLElement($head);
        foreach ($this->params as $idx => $itm) {
            if (!$itm) {
                continue;
            }
            $xml->addChild($idx, $itm);
        }
        return $xml->asXML();
    }

}