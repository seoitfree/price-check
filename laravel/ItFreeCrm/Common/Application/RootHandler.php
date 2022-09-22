<?php
namespace ItFreeCrm\Common\Application;


use ItFreeCrm\Common\Application\Request\Request;

abstract class RootHandler
{
    protected ResultHandler $resultHandler;

    public function __construct()
    {
        $this->resultHandler = new ResultHandler();
    }

    abstract public function handle(Request $request): ResultHandler;

    public function __invoke(Request $request)
    {
        if ($request->getRules() && !$this->validate($request)) {
            return $this->resultHandler;
        }
        return $this->handle($request);
    }

    private function validate(Request $request): bool
    {
        $validator = $request->validate();


        if ($validator->fails()) {
            $this->resultHandler
                ->setErrors($this->getErrors($validator))
                ->setStatusCode(422);

            return false;
        }

        return true;
    }

    private function getErrors(\Illuminate\Contracts\Validation\Validator $validator): array
    {
        $errors = [];

        foreach ($validator->errors()->toArray() as $field => $error) {
            $errors[$field] = ["message" => $error[0]];
        }

        return [$errors];
    }
}
