<?php

class AIProviderException extends RuntimeException
{
    private string $provider;
    private int $httpStatus;

    /**
     * @param string $message
     * @param string $provider
     * @param int $httpStatus
     * @param Throwable|null $previous
     */
    public function __construct(string $message, string $provider, int $httpStatus = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $httpStatus, $previous);
        $this->provider = $provider;
        $this->httpStatus = $httpStatus;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return int
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
