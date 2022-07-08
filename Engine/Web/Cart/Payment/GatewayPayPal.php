<?php

namespace SymfonyCMS\Engine\Web\Cart\Payment;

use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayFactory;
use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Ramsey\Uuid\Uuid;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GatewayPayPal extends AbstractGateway
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
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => $order->title,
                    "amount" => [
                        "value" => $order->total,
                        "currency_code" => "NZD"
                    ]
                ]
            ],
            "payer" => [
                "email_address" => $order->email,
                "name" => [
                    "given_name" => ($order->isPickup == 1 ? $order->pickupFirstName : ($order->billingSame ? $order->shippingFirstName : $order->billingFirstName)),
                    "surname" => ($order->isPickup == 1 ? $order->pickupLastName : ($order->billingSame ? $order->shippingFirstName : $order->billingLastName)),
                ],
            ],
            "application_context" => [
                "cancel_url" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
                "return_url" => $request->getSchemeAndHttpHost() . '/checkout/finalise',
            ]
        ];

        if ($order->billingSame && $order->shippingPhone) {
            $query['payer']['phone'] = [
                "phone_type" => "MOBILE",
                "phone_number" => [
                    "national_number" => preg_replace('/[^0-9]+/', '', $order->shippingPhone)
                ]
            ];
        } elseif (!$order->billingSame && $order->billingPhone) {
            $query['payer']['phone'] = [
                "phone_type" => "MOBILE",
                "phone_number" => [
                    "national_number" => preg_replace('/[^0-9]+/', '', $order->billingPhone)
                ]
            ];
        }

        if (!$order->isPickup) {
            $query['payer']['address'] = [
                "address_line_1" => $order->shippingAddress,
                'admin_area_2' => $order->shippingCity,
                "postal_code" => $order->shippingPostcode,
                "country_code" => $order->shippingCountry,
            ];
        }

        try {
            $payPalRequest = new OrdersCreateRequest();
            $payPalRequest->prefer('return=representation');
            $payPalRequest->body = $query;

            $client = $this->getClient();
            // Call API with your client and get a response for your call
            $result = $client->execute($payPalRequest);
            $jsonData = $result;

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


        $paymentUrl = null;
        $token = null;
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->result)) {
            $token = $jsonData->result->id;
            foreach ($jsonData->result->links as $itm) {
                if ($itm->rel == 'approve') {
                    $paymentUrl = $itm->href;
                }
            }
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

        try {
            // Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
            // $response->result->id gives the orderId of the order created above
            $request = new OrdersCaptureRequest($order->payToken);
            $request->prefer('return=representation');

            $client = $this->getClient();
            // Call API with your client and get a response for your call
            $result = $client->execute($request);
            $jsonData = $result;

        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        $end = time();
        $seconds = $end - $start;
        $this->addToOrderLog(
            $order,
            $this->getId() . ' - ' . __FUNCTION__,
            '',
            $order->payToken,
            json_encode($result, JSON_PRETTY_PRINT),
            1,
            $seconds
        );

        $status = null;
        if (isset($jsonData) && gettype($jsonData) == 'object' && isset($jsonData->result)) {
            $status = $jsonData->result->status == 'COMPLETED' ? 1 : 0;
        }

        return $this->finaliseOrderAndRedirect($order, $status);
    }

    /**
     * @return PayPalHttpClient
     */
    private function getClient()
    {
        if (getenv('PAYPAL_CLIENT_TEST') == 1) {
            $env = new SandboxEnvironment(getenv('PAYPAL_CLIENT_ID'), getenv('PAYPAL_CLIENT_SECRET'));
        } else {
            $env = new ProductionEnvironment(getenv('PAYPAL_CLIENT_ID'), getenv('PAYPAL_CLIENT_SECRET'));
        }
        return new PayPalHttpClient($env);
    }
}