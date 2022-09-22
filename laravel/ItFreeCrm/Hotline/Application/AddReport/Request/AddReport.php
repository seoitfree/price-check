<?php

namespace ItFreeCrm\Hotline\Application\AddReport\Request;


use ItFreeCrm\Common\Application\Request\Request;
use ItFreeCrm\Common\Application\Request\ValidationInterface;

final class AddReport extends Request
{
    private string $urlReportHotline;

    public function __construct(array $data = [])
    {
        $this->urlReportHotline = !empty($data['url_report_hotline']) ? trim($data['url_report_hotline']) : "";

        $this->setRules();
    }

    public function urlReportHotline(): string {
        return $this->urlReportHotline;
    }

    private function setRules(): void {
        $this->rules = new class() implements ValidationInterface {
            public function getRules(): array {
                return [
                    'url_report_hotline' => 'required|string|url'
                ];
            }

            public function messages(): array {
                return [];
            }
        };
    }

    public function toValidate(): array {
        return [
            'url_report_hotline' => $this->urlReportHotline
        ];
    }
}
