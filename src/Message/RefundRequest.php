<?php

namespace Omnipay\YandexKassa\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Throwable;

class RefundRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('transactionReference', 'description', 'amount', 'currency');

        return [
            'transactionReference' => $this->getTransactionReference(),
            'description' => $this->getDescription(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
        ];
    }

    public function sendData($data)
    {
        $request = [
            'paymentId' => $data['transactionReference'],
            'description' => $data['description'],
            'amount' => [
                'value' => $data['amount'],
                'currency' => $data['currency'],
            ],
        ];

        try {
            $refundResponse = $this->client->createRefund($request, $this->makeIdempotencyKey());

            return $this->response = new RefundResponse($this, $refundResponse);
        } catch (Throwable $e) {
            throw new InvalidRequestException('Failed to request refund: ' . $e->getMessage(), 0, $e);
        }
    }

    private function makeIdempotencyKey(): string
    {
        return uniqid('', true);
    }
}
