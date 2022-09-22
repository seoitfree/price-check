<?php

namespace ItFreeCrm\Common\UI\Traits;

use ItFreeCrm\Common\Application\ResultHandler;
use \Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function getResponse(ResultHandler $resultHandler): JsonResponse
    {
        $hasErrors = $resultHandler->hasErrors();

        return response()->json([
            "data" => $hasErrors ? [] : $resultHandler->getResult(),
            "errors" => $hasErrors ? $resultHandler->getErrors() : [],
        ], $resultHandler->getStatusCode());
    }
}
