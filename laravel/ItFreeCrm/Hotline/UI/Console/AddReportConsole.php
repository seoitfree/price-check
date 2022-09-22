<?php

namespace ItFreeCrm\Hotline\UI\Console;


use Illuminate\Console\Command;
use ItFreeCrm\Common\Application\ResultHandler;
use ItFreeCrm\Hotline\Application\AddReport\AddReportConsoleHandler;
use ItFreeCrm\Hotline\Application\AddReport\Request\AddReport;

class AddReportConsole extends Command
{
    /**
     * @var string
     */
    protected $signature = 'report:add {--url=}'; //php artisan report:add --url=https://hotline.ua/cabinet/23876/analytics/load-report/1908/?hash=6a83f476a6ee13284e6f491a4fe453d2

    /**
     * @var string
     */
    protected $description = 'Added report for hotline';

    public function handle()
    {
        $this->info($this->description);

        $resultHandler = (new AddReportConsoleHandler($this, $this->output))(new AddReport([
            'url_report_hotline' => $this->option('url')
        ]));

        if ($resultHandler->hasErrors()) {
            $this->outputError($resultHandler);
        } else {
            $this->info("Finished");
        }
    }

    /**
     * @param ResultHandler $resultHandler
     * @return void
     */
    private function outputError(ResultHandler $resultHandler): void
    {
        $error = $resultHandler->getErrors();

        if ($resultHandler->getStatusCode() === 422) {
            $this->error($error[0]['url_report_hotline']["message"]);
        }
    }
}
