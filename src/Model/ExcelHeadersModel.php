<?php

declare(strict_types=1);

namespace App\Model;

final class ExcelHeadersModel
{
    public function __construct(protected array $headers, protected array $expectedheaders)
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
     * @return array
     */
    public function getExpectedheaders(): array
    {
        return $this->expectedheaders;
    }
}
