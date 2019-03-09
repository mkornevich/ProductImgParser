<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 26.02.2019
 * Time: 0:15
 */

namespace App;


use App\Base\BaseSite;
use App\Module\CSV;
use App\Module\HTTP;
use App\Module\IO;
use App\Site\ECatalogRuSite;
use App\Site\OnlinerBySite;
use App\Site\SocketBySite;

class Main
{

    const SLEEP_COUNT = 5; // на сколько засыпать после SLEEP_AFTER циклов
    const SLEEP_AFTER = 15; // через сколько циклов засыпать на SLEEP_COUNT раз
    const SLEEP_EACH = 1000000; // после каждого цикла на сколько засыпать в микросекундах. 1с = 1000000 micro_seconds

    // константы для определения столбца в input.csv
    const ARTICLE = 0;
    const QUERY = 1;
    const IMG_COUNT = 2;
    const STATUS = 3;
    const FOUND_COUNT = 4;
    const SEARCH_SERVICE = 5;


    public function main()
    {
        IO::writeLn("==========================================");
        IO::writeLn("#        img parser by mkornevich        #");
        IO::writeLn("==========================================");

        $sites = $this->getSites();

        IO::clearDir(data('output'));

        $inputCsv = CSV::read(data('input.csv'));

        foreach ($inputCsv as $key => &$row) {
            try {
                $row = $this->handleCsvRow($key, $sites, new InputRow($row))->getRowData();
            } catch (\Exception $e) {
                IO::writeLn("ERROR IN " . $key . " ITEM");
                IO::writeLn($e->getMessage());
                IO::writeLn("END ERROR");
            }

        }

        CSV::write(data('input.csv'), $inputCsv);

        IO::writeLn();
        IO::writeLn("END :)");

    }

    /**
     * @param int $rowKey
     * @param BaseSite[] $sites
     * @param InputRow $rowData
     * @return InputRow
     */
    private function handleCsvRow($rowKey, $sites, $rowData)
    {
        IO::writeLn();
        IO::writeLn(">>> start parsing " . $rowKey . " item with article " . $rowData->article . " with query " . $rowData->searchQuery);

        $parseSiteName = "none";
        $parseSite = null;
        $searchCount = 0;
        $parseProductUrl = null;
        $parseImgLinks = null;
        $parseImgCount = 0;

        $searchSteps = [
            ['site_name' => 'e-catalog.ru', 'query' => $rowData->searchQuery],
            ['site_name' => 'socket.by', 'query' => $rowData->searchQuery],
            ['site_name' => 'onliner.by', 'query' => $rowData->searchQuery],
            ['site_name' => 'onliner.by', 'query' => $rowData->searchQuery1],
            ['site_name' => 'socket.by', 'query' => $rowData->searchQuery1],
            ['site_name' => 'e-catalog.ru', 'query' => $rowData->searchQuery1],
        ];

        foreach ($searchSteps as $searchStep) {
            $siteName = $searchStep['site_name'];
            $site = $sites[$siteName];
            $query = $searchStep['query'];

            IO::writeLn('search products in "' . $siteName . '" by query "' . $query . '"');
            if ($query == '0') {
                IO::write(" -> SKIPPED");
                continue;
            }
            $findProducts = $site->getProductsBySearchQuery($query);
            $searchCount = count($findProducts);
            IO::write(" - found " . $searchCount . " products");

            if ($searchCount > 0) {
                $parseSite = $site;
                $parseProductUrl = $findProducts[0]['url'];
                $parseSiteName = $siteName;
            }

            if ($searchCount == 1) {
                IO::writeLn("get image links from " . $parseProductUrl);
                $parseImgLinks = $parseSite->getImgLinksByProductUrl($parseProductUrl);
                $parseImgCount = count($parseImgLinks);
                if ($parseImgCount > 0) {
                    IO::write(" -> OK found " . $parseImgCount . ' images');
                    break;
                } else {
                    IO::write(" -> FAIL images not found");
                    //break;
                }
            };

        }

        if ($parseImgCount > 0) {

            IO::writeLn("saving images");
            mkdir(data("output/" . $rowData->article));
            foreach ($parseImgLinks as $key => $imageLink) {
                HTTP::saveImg(data("output/" . $rowData->article . '/' . ($key + 1) . '.jpg'), $imageLink);
                if ($key + 1 == $rowData->imgLimit) break;
            }
            IO::write(" -> OK");

        }

        $rowData->searchService = $parseSiteName;
        $rowData->searchCount = $searchCount;
        $rowData->status = ($parseImgCount > 0) ? 1 : 0;

        if ($parseImgCount > 0) {
            $this->handleSleep($rowKey);
        }
        return $rowData;
    }

    /**
     * @return BaseSite[]
     */
    private function getSites()
    {
        return [
            'e-catalog.ru' => new ECatalogRuSite(),
            'socket.by' => new SocketBySite(),
            'onliner.by' => new OnlinerBySite(),
        ];
    }

    private function handleSleep($itemIndex)
    {
        if (($itemIndex + 1) % self::SLEEP_AFTER == 0) {
            echo "\n\nSleep " . self::SLEEP_COUNT . " seconds.\n\n";
            sleep(self::SLEEP_COUNT);
        }

        usleep(self::SLEEP_EACH);
    }

}