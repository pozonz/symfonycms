<?php

namespace ExWife\Engine\Web\_Core\Base\Controller;

use ExWife\Engine\Web\_Core\Base\Controller\Traits\WebControllerTrait;
use ExWife\Engine\Cms\_Core\Base\Controller\BaseController;
use ExWife\Engine\Cms\_Core\Model\Model;
use ExWife\Engine\Cms\_Core\Service\CmsService;
use ExWife\Engine\Cms\_Core\Service\UtilsService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

/**
 * Class WebController
 * @package ExWife\Engine\Cms\_Core\Controller
 */
class WebController extends BaseController
{
    const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

    use WebControllerTrait;

    /**
     * @route("/order/test/email")
     * @param Request $request
     * @param Environment $environment
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function testOrderEmail(Request $request, Environment $environment, \Swift_Mailer $mailer)
    {
        $orderId = $request->get('order_id');
        $token = $request->get('secret');
        if ($token !== static::TOKEN) {
            throw new NotFoundHttpException();
        }

        $order = Order::getByField($this->_connection, 'title', $orderId);
        $messageBody = $this->getOrderEmailHtml($order, $environment);

        $message = (new \Swift_Message())
            ->setSubject("Your Bedpost order has been received - #{$order->getTitle()}")
            ->setFrom([
                getenv('EMAIL_FROM') => 'Bedpost',
            ])
            ->setTo(['weida@iicnz.com', 'weida@gravitate.co.nz', 'jamie@gravitate.co.nz'])
            ->setBody(
                $messageBody, 'text/html'
            );
        $result = $mailer->send($message);

        return new Response($result);
    }

    /**
     * @route("/order/test/email/html")
     * @param Request $request
     * @param Environment $environment
     * @return Response
     */
    public function testOrderEmailHtml(Request $request, Environment $environment)
    {
        $orderId = $request->get('order_id');
        $token = $request->get('secret');
        if ($token !== static::TOKEN) {
            throw new NotFoundHttpException();
        }

        $fullClassName = UtilsService::getFullClassFromName('Order');
        $order = $fullClassName::getByField($this->_connection, 'title', $orderId);
        $messageBody = $this->getOrderEmailHtml($order, $environment);
        return new Response($messageBody, 200, [
            'Content-Type' => 'text/html',
        ]);
    }

    /**
     * @route("/{page}", requirements={"page" = ".*"})
     * @return Response
     */
    public function web(Request $request)
    {
        $params = $this->getParamsByRequest($request);
        return $this->render($params['theNode']->objPageTemplate()->fileName, $params);
    }

    /**
     * @param $order
     * @param $environment
     */
    protected function getOrderEmailHtml($order, $environment)
    {
        return $environment->render('cart/email-invoice.twig', array(
            'order' => $order,
        ));
    }
}
