<?php
/**
 * Yandex.Kassa driver for Omnipay payment processing library
 *
 * @link      https://github.com/hiqdev/omnipay-yandex-kassa
 * @package   omnipay-yandex-kassa
 * @license   MIT
 * @copyright Copyright (c) 2019, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\YandexKassa;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\YandexKassa\Message\CaptureRequest;
use Omnipay\YandexKassa\Message\CaptureResponse;
use Omnipay\YandexKassa\Message\DetailsRequest;
use Omnipay\YandexKassa\Message\DetailsResponse;
use Omnipay\YandexKassa\Message\IncomingNotificationRequest;
use Omnipay\YandexKassa\Message\PurchaseRequest;
use Omnipay\YandexKassa\Message\RefundRequest;
use Omnipay\YandexKassa\Message\RefundResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use YooKassa\Client;
use YooKassa\Client\CurlClient;

/**
 * Class Gateway.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class Gateway extends AbstractGateway
{
    /**
     * Seconds to wait for the connection (TCP + TLS) to the YooKassa API.
     *
     * The SDK default is 30s, which is unusable for a call made while an incoming webhook is being
     * served: one unreachable API pins the worker for half a minute before anyone can retry.
     */
    const DEFAULT_CONNECTION_TIMEOUT = 5;

    /** Seconds to wait for the whole round trip (SDK default is 80s, see above). */
    const DEFAULT_TIMEOUT = 15;

    /** @var Client|null */
    private $yandexClient;

    public function __construct(ClientInterface $httpClient = null, HttpRequest $httpRequest = null)
    {
        parent::__construct($httpClient, $httpRequest);
    }

    protected function getYandexClient(): Client
    {
        if ($this->yandexClient === null) {
            $this->yandexClient = new Client();
            $this->yandexClient->setAuth($this->getShopId(), $this->getSecret());

            $apiClient = $this->yandexClient->getApiClient();
            if ($apiClient instanceof CurlClient) {
                $apiClient->setConnectionTimeout($this->getConnectionTimeout());
                $apiClient->setTimeout($this->getTimeout());
            }
        }

        return $this->yandexClient;
    }

    public function getConnectionTimeout()
    {
        return $this->getParameter('connectionTimeout') ?: self::DEFAULT_CONNECTION_TIMEOUT;
    }

    public function setConnectionTimeout($value)
    {
        return $this->setParameter('connectionTimeout', $value);
    }

    public function getTimeout()
    {
        return $this->getParameter('timeout') ?: self::DEFAULT_TIMEOUT;
    }

    public function setTimeout($value)
    {
        return $this->setParameter('timeout', $value);
    }

    public function getName()
    {
        return 'Yandex.Kassa';
    }

    public function getShopId()
    {
        return $this->getParameter('shopId');
    }

    public function setShopId($value)
    {
        return $this->setParameter('shopId', $value);
    }

    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    /**
     * @param array $parameters
     * @return PurchaseRequest|\Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(PurchaseRequest::class, $this->injectYandexClient($parameters));
    }

    public function completePurchase(array $parameters = [])
    {
        return $this->details($parameters);
    }

    /**
     * @param array $parameters
     * @return CaptureResponse|\Omnipay\Common\Message\AbstractRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(CaptureRequest::class, $this->injectYandexClient($parameters));
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|DetailsRequest
     */
    public function details(array $parameters = [])
    {
        return $this->createRequest(DetailsRequest::class, $this->injectYandexClient($parameters));
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|DetailsResponse
     */
    public function notification(array $parameters = [])
    {
        return $this->createRequest(IncomingNotificationRequest::class, $this->injectYandexClient($parameters));
    }

    /**
     * @param array $parameters
     * @return RefundResponse|\Omnipay\Common\Message\AbstractRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest(RefundRequest::class, $this->injectYandexClient($parameters));
    }

    private function injectYandexClient(array $parameters): array
    {
        $parameters['yandexClient'] = $this->getYandexClient();

        return $parameters;
    }
}
