<?php

namespace ItFreeCrm\Hotline\Application\AddReport;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ItFreeCrm\Common\Application\Request\Request;
use ItFreeCrm\Common\Application\ResultHandler;
use ItFreeCrm\Common\Application\RootHandler;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Console\Command as Console;
use Symfony\Component\DomCrawler\Crawler;
use SimpleXMLElement;
use Illuminate\Console\OutputStyle;

class AddReportConsoleHandler extends RootHandler
{
    const HEADER = ["id", "url_path", "title", "price", "min_price", "leader_price"];

    const SHOPS = [
        "COMFY.UA" => '',
        "ФОКСТРОТ" => '',
        "Rozetka.ua" => '',
        "MOYO" => '',
        "Епіцентр" => '',
        "Цитрус" => '',
        "Brain.Комп'ютери/гаджети" => '',
        "ALLO.ua" => '',
        "Ельдорадо" => '',
        "DEX.UA" => '',
        "KELA" => '',
        "GRANADO" => '',
        "BS-partner.com.ua" => '',
        "BS-Market.com.ua" => '',
        "VENCON.UA" => '',
        "fiskars-official" => '',
        "kenwood-shop.com.ua" => '',
        "Polaris-shop.com.ua" => '',
        "Philips Domestic Appliances" => '',
        "Gastroshop" => ''
    ];

    private Console $console;
    private OutputStyle $outputStyle;

    public function __construct(Console $console, OutputStyle $outputStyle)
    {
        $this->console = $console;
        $this->outputStyle = $outputStyle;

        parent::__construct();
    }

    /**
     * @param Request $request
     * @return ResultHandler
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(Request $request): ResultHandler
    {
        $response = (new Client(['base_uri' => $request->urlReportHotline()]))->request('GET');

        if ($response->getStatusCode() === 200) {
            $fileName = $this->createFileReportForParser($response);

            $this->console->info("Start parse data from Hotline");

            $this->parse($fileName);

            Storage::disk('local')->delete("docs/$fileName");
        } else {
            $this->resultHandler->setErrors(["Bad request to Hotline report"]);
        }

        return $this->resultHandler;
    }

    /**
     * @param ResponseInterface $response
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    private function createFileReportForParser(ResponseInterface $response): string
    {
        $this->console->info("Start created file for parse (convert XML to CSV)");

        $fileName = 'report_before_hotline' . time() . '.csv';
        Storage::disk('local')->put("docs/$fileName", '');

        $csv = Writer::createFromPath(storage_path('app/docs/') . $fileName , 'w+');
        $csv->insertOne(static::HEADER);
        $this->convertXMLtoCSV($response, $csv);

        return $fileName;
    }

    /**
     * @param ResponseInterface $response
     * @param Writer $csv
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    private function convertXMLtoCSV(ResponseInterface $response, Writer $csv): void
    {
        $movies = new SimpleXMLElement($response->getBody()->getContents());

        foreach ($movies->items->item as $item) {
            $csv->insertOne([
                "id" => (string)$item->ids->id,
                "url_path" => (string)$item->url_path,
                "title" => (string)$item->title,
                "price" => (string)$item->price,
                "min_price" => (string)$item->min_price,
                "leader_price" => (string)$item->leader_price
            ]);
        }
    }

    /**
     * @param string $fileName
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    private function parse(string $fileName): void
    {
        $records = Statement::create()->process($this->getReader($fileName));

        $iteratorRecords = $records->getRecords();
        $iteratorRecords->next();

        $csv = $this->getWriter();
        $bar = $this->outputStyle->createProgressBar(count(file(storage_path('app/docs/') . $fileName)));

        $bar->start();
        while ($iteratorRecords->valid()) {
            $record = $iteratorRecords->current();

            $contents = file_get_contents($record[1]);
            $crawler = new Crawler($contents);

            if (strripos($contents, 'class="captcha container') !== false) {
                $this->console->newLine();
                if ($this->console->confirm('Check captcha and continue. Do you wish to continue?', true)) {
                    $shopsPrice = $this->getData($record, new Crawler(file_get_contents($record[1])));

                    $csv->insertOne( $shopsPrice);
                }
            } else {
                $shopsPrice = $this->getData($record, $crawler);

                $csv->insertOne( $shopsPrice);
            }

            sleep(3);
            $bar->advance();
            $iteratorRecords->next();
        }
        $bar->finish();
    }

    /**
     * @param $fileName
     * @return Writer
     * @throws \League\Csv\CannotInsertRecord
     */
    private function getWriter(): Writer
    {
        $fileNameAfter = 'report_hotline' . time() . '.csv';

        $csv = Writer::createFromPath(storage_path('app/docs/') . $fileNameAfter , 'w+');

        $csv->insertOne(array_merge(static::HEADER, array_keys(static::SHOPS)));

        return $csv;
    }

    /**
     * @param $fileName
     * @return Reader
     */
    private function getReader($fileName): Reader
    {
        return Reader::createFromPath(storage_path('app/docs/') . $fileName, 'r');
    }//php artisan report:add --url=https://hotline.ua/cabinet/37865/analytics/load-report/1909/?hash=af2e99e7bb69f0b83c98cab58055ef64

    /**
     * @param array $items
     * @param Crawler $crawler
     * @return array|string[]
     */
    private function getData(array $items, Crawler $crawler): array
    {
        return [
            "product_id" => $items[0],
            "url_path" => $items[1],
            'title' => $items[2],
            'price' => $items[3],
            'min_price' => $items[4],
            'leader_price' => $items[5],
        ] + $this->getShops($crawler);
    }

    /**
     * @param Crawler $crawler
     * @return string[]
     */
    private function getShops(Crawler $crawler): array
    {
        $shops = static::SHOPS;

        foreach ($crawler->filter('.list > .list__item') as $domElement) {
            $shopTitle = '';

            foreach ($domElement->getElementsByTagName('a') as $item) {
                if (explode(' ', $item->getAttribute('class'))[0] === 'shop__title') {
                    $shopTitle = trim($item->textContent);
                }
                if (explode(' ', $item->getAttribute('class'))[0] === 'info__price-actual') {
                    if (array_key_exists($shopTitle, $shops)) {
                        $shops[$shopTitle] = trim(str_replace("грн", "", $item->textContent));
                    }
                };
            }
        }

        return $shops;
    }
}
