<?php

declare(strict_types=1);

namespace App\Model;

final class ExcelHeadersModel
{
    private array $columns;

    public function __construct(protected array $expectedColumns)
    {
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function getExpectedColumns(): array
    {
        return $this->expectedColumns;
    }
}
