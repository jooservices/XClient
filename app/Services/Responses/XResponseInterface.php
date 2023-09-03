<?php

namespace App\Services\Responses;

interface XResponseInterface
{
    public function isSuccessful(): bool;

    public function getStatusCode(): int;

    public function getResponse(): ?string;

    public function getHeaders(): array;

    public function getBody(): ?string;

    public function getVersion(): string;

    public function getReason(): ?string;

    public function getData(): mixed;
}
