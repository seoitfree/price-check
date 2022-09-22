<?php

namespace ItFreeCrm\Common\Application\Request;

interface ValidationInterface
{
    /**
     * @return array
     */
    public function getRules(): array;

    /**
     * @return array
     */
    public function messages(): array;
}
