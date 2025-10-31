<?php

namespace WechatOfficialAccountMenuBundle\Exception;

class WechatApiException extends \RuntimeException
{
    public function __construct(string $message, private readonly int $errorCode, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
