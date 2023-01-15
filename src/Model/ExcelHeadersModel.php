<?php

declare(strict_types=1);

namespace App\Model;

final class ExcelHeadersModel
{
    private array $headers;

    public function __construct(protected array $expectedHeaders)
    {
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getExpectedHeaders(): array
    {
        return $this->expectedHeaders;
    }
}
