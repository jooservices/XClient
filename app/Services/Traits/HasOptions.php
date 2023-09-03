<?php

namespace App\Services\Traits;

use Illuminate\Support\Collection;

trait HasOptions
{
    private Collection $options;

    public function bootHasOptions(): void
    {
        $this->options = collect();
    }

    public function addOptions(array $options): self
    {
        $this->options->merge($options);

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = collect($options);

        return $this;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options->get($key, $default);
    }

    public function hasOption(string $key): bool
    {
        return $this->options->has($key);
    }

    public function removeOption(string $key): self
    {
        $this->options->forget($key);

        return $this;
    }

    public function getOptionsArray(): array
    {
        return $this->options->toArray();
    }
}
