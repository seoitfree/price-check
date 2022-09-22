<?php
namespace ItFreeCrm\Common\Application\Request;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as Validation;

abstract class Request
{
    protected ValidationInterface $rules;

    public function validate(): Validator
    {
        return Validation::make(
            $this->toValidate(),
            $this->rules->getRules(),
            $this->rules->messages()
        );
    }

    public function getRules(): ValidationInterface {
        return $this->rules;
    }
}
