<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\FileUpload;

final class DataImportStats
{
    private FileUpload $file;
    private array $imported = [];
    private array $alreadyExist = [];

    public function setFile(FileUpload $file): void
    {
        $this->file = $file;
    }

    public function getFile(): FileUpload
    {
        return $this->file;
    }

    public function addImported(string $name): void
    {
        $this->imported[] = $name;
    }

    public function getImported(): array
    {
        return $this->imported;
    }

    public function getImportedCount(): int
    {
        return \count($this->imported);
    }

    public function addAlreadyExist(string $name): void
    {
        $this->alreadyExist[] = $name;
    }

    public function getAlreadyExist(): array
    {
        return $this->alreadyExist;
    }

    public function getAlreadyExistCount(): int
    {
        return \count($this->alreadyExist);
    }
}
