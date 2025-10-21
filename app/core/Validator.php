<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function require(string $field, ?string $value, string $message): self
    {
        if ($value === null || trim($value) === '') {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function email(string $field, ?string $value, string $message): self
    {
        if ($value !== null && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function minLength(string $field, ?string $value, int $min, string $message): self
    {
        if ($value !== null && mb_strlen($value) < $min) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function numeric(string $field, ?string $value, string $message): self
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function unique(callable $callback, string $field, ?string $value, string $message): self
    {
        if ($value !== null && $value !== '' && $callback($value) === true) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }
}

