<?php
/**
 * Yandex.Kassa driver for Omnipay payment processing library
 *
 * @link      https://github.com/hiqdev/omnipay-yandex-kassa
 * @package   omnipay-yandex-kassa
 * @license   MIT
 * @copyright Copyright (c) 2019, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\YandexKassa\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class DetailsRequest.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class DetailsRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('transactionReference');

        return [];
    }

    /**
     * Send the request with specified data.
     *
     * @param  mixed $data The data to send
     * @throws InvalidResponseException
     * @return DetailsResponse|ResponseInterface
     */
    public function sendData($data): ResponseInterface
    {
        try {
            $response = $this->client->getPaymentInfo($this->getTransactionReference());

            return new DetailsResponse($this, $response);
        } catch (\Throwable $e) {
            // Keep the original exception: a curl-level failure arrives as ApiConnectionException
            // with code 0, which is indistinguishable from "the gateway answered, but not with what
            // we expected" once flattened into InvalidResponseException. Callers need that difference
            // to tell a transport hiccup (retry) from a declined payment (final).
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
