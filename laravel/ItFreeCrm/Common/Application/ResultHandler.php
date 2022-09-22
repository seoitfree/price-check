<?php

namespace ItFreeCrm\Common\Application;

class ResultHandler
{
    private array $errors = [];
    private array $result = [];
    private int $code = 200;

    public function checkAuthResponse(): bool {
        return $this->auth;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function setErrors(array $errors): ResultHandler
    {
        $this->errors = $errors;

        return $this;
    }

    public function setResult(array $result): ResultHandler
    {
        $this->result = $result;

        return $this;
    }

    public function getResult(): array {
        return $this->result;
    }

    public function setStatusCode(int $code = 200): ResultHandler
    {
        $this->code = $code;

        return $this;
    }

    public function getStatusCode(): int {
        return $this->code;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }
}
