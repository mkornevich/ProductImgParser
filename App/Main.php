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
        try {

            IO::writeLn("==========================================");
            IO::writeLn("#        img parser by mkornevich        #");
            IO::writeLn("==========================================");

            $sites = $this->getSites();

            IO::clearDir(data('output'));

            $inputCsv = CSV::readWithNames(data('input.csv'));

            foreach ($inputCsv as $key => &$row) {
                try {
                    $row = $this->handleCsvRow($key, $sites, $row);

                } catch (\Exception $e) {
                    $this->printError($e, $key . ' ITEM');
                }

            }

            CSV::writeWithNames(data('input.csv'), $inputCsv);

            IO::writeLn();
            IO::writeLn("END :)");
        }catch (\Exception $e){
            $this->printError($e, 'PARSER');
            IO::writeLn();
            IO::writeLn("END :(");
        }

    }

    /**
     * @param int $rowKey
     * @param BaseSite[] $sites
     * @param InputRow $row
     * @return InputRow
     */
    private function handleCsvRow($rowKey, $sites, $row)
    {
        IO::writeLn();
        IO::writeLn(">>> start parsing " . $rowKey . " item with article " . $row['in_article'] . " with query " . $row['in_search_query']);

        $G_SiteName = "none";
        $G_Site = null;
        $G_ProductSearchCount = 0;
        $G_ProductUrl = null;
        $G_ImgLinks = [];
        $G_ImgCount = 0;

        $searchSiteNames = preg_split('/\|/', $row['in_search_order']);

        foreach ($searchSiteNames as $searchSiteName) {
            if(!isset($sites[$searchSiteName])){
                IO::writeLn(">>> SITE " . $searchSiteName . " incorrect in article " . $row['in_article']);
                IO::writeLn(">>> FOUNT SITES:");
                foreach ($sites as $name => $handler){
                    IO::writeLn(">>> " . $name);
                }
                continue;
            }

            $site = $sites[$searchSiteName];
            $query = $row['in_search_query'];

            if($row['in_onliner_id'] != '0' && $searchSiteName == 'onliner.by'){

                IO::writeLn('search product in "' . $searchSiteName . '" by onliner_id "' . $row['in_onliner_id'] . '"');

                try {
                    $productJsonData = HTTP::getJSONDocument('https://catalog.api.onliner.by/products/' . $row['in_onliner_id']);
                }catch (\Exception $e){
                    IO::writeLn('information about onliner_id not found >>> ' . $e->getMessage());
                    continue;
                }

                $G_Site = $site;
                $G_ProductUrl = $productJsonData['html_url'];
                $G_SiteName = $searchSiteName;
                $G_ProductSearchCount = 1;

                IO::writeLn("get image links from " . $G_ProductUrl);

                $G_ImgLinks = $G_Site->getImgLinksByProductUrl($G_ProductUrl);
                $G_ImgCount = count($G_ImgLinks);
                if ($G_ImgCount > 0) {
                    IO::write(" -> OK found " . $G_ImgCount . ' images');
                    break;
                } else {
                    IO::write(" -> FAIL images not found");
                    //break;
                }
            }else {

                IO::writeLn('search products in "' . $searchSiteName . '" by query "' . $query . '"');

                if ($query == '0') {
                    IO::write(" -> SKIPPED");
                    continue;
                }

                $findProducts = $site->getProductsBySearchQuery($query);
                $G_ProductSearchCount = count($findProducts);

                IO::write(" - found " . $G_ProductSearchCount . " products");

                if ($G_ProductSearchCount > 0) {
                    $G_Site = $site;
                    $G_ProductUrl = $findProducts[0]['url'];
                    $G_SiteName = $searchSiteName;
                }

                if ($G_ProductSearchCount == 1) {
                    IO::writeLn("get image links from " . $G_ProductUrl);
                    $G_ImgLinks = $G_Site->getImgLinksByProductUrl($G_ProductUrl);
                    $G_ImgCount = count($G_ImgLinks);
                    if ($G_ImgCount > 0) {
                        IO::write(" -> OK found " . $G_ImgCount . ' images');
                        break;
                    } else {
                        IO::write(" -> FAIL images not found");
                        //break;
                    }
                }
            }

        }




        $this->saveImages($G_ImgLinks, $row['in_article'], $row['in_img_limit']);

        $row['out_search_site'] = $G_SiteName;
        $row['out_search_result_count'] = $G_ProductSearchCount;
        $row['out_status_code'] = ($G_ImgCount > 0) ? 1 : 0;

        if ($G_ImgCount > 0) {
            $this->handleSleep($rowKey);
        }
        return $row;
    }

    private function printError($exception, $errorIn){
        IO::writeLn("==========>>>> ERROR IN " . $errorIn . " <<<<==========");
        IO::writeLn('=== error message ===');
        IO::writeLn($exception->getMessage());
        IO::writeLn('=== error file and number ===');
        IO::writeLn('ERROR_FILE_NAME = ' . $exception->getFile());
        IO::writeLn('ERROR_FILE_LINE = ' . $exception->getLine());
        IO::writeLn('=== error stack trace ===');
        IO::writeLn($exception->getTraceAsString());
        IO::writeLn("==========>>>> END ERROR <<<<==========");
    }

    private function saveImages($imageLinks, $articleId, $imgLimit){
        if (count($imageLinks) > 0) {
            IO::writeLn("save " . $imgLimit . " images");
            mkdir(data("output/" . $articleId));
            foreach ($imageLinks as $key => $imageLink) {
                HTTP::saveImg(data("output/" . $articleId . '/' . ($key + 1) . '.jpg'), $imageLink);
                if ($key + 1 == $imgLimit) break;
            }
            IO::write(" -> OK");
        }
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