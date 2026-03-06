<?php

namespace Omnipay\YandexKassa\Message;

use Omnipay\Common\Message\AbstractResponse;
use YooKassa\Request\Refunds\CreateRefundResponse;

class RefundResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return $this->data->getStatus() === 'succeeded';
    }

    public function getTransactionReference()
    {
        return $this->data->getId();
    }

    public function getMessage()
    {
        return $this->data->getStatus();
    }
}
