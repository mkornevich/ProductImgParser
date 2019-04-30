<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 25.02.2019
 * Time: 20:36
 */

namespace App\Site;


use App\Base\BaseSite;
use App\Module\HTTP;

class CorizzaBySite extends BaseSite
{

    public function getProductsBySearchQuery($query, $args = [])
    {
        $products = [];
        $document = HTTP::getPQDocument('https://corizza.by/search/index.php?spell=1&q=' . rawurlencode($query));
        $links = $document->find('div.search-page div.catalog_item > a');
        foreach ($links as $link) {
            $products[] = [
                'url' => 'https://corizza.by' . pq($link)->attr('href')
            ];
        }
        return $products;
    }

    public function getImgLinksByProductUrl($url, $args = [])
    {
        $links = [];

        $htmlText = HTTP::get($url);

        $startPos = mb_strpos($htmlText, 'JCCatalogElement(') + 17;
        $endPos = mb_strpos($htmlText, ");\n", $startPos);

        $jsonText = mb_substr($htmlText, $startPos, $endPos - $startPos);

        $replaceMap = [ // для преобразования в нормальный json формат
            "{'" => '{"',
            "'}" => '"}',
            "':" => '":',
            ":'" => ':"',
            ",'" => ',"',
            "'," => '",',
            "['" => '["',
            "']" => '"]',
        ];

        foreach($replaceMap as $search => $replace){
            $jsonText = str_replace($search, $replace, $jsonText);
        }

        $json = json_decode($jsonText, true);

        foreach ($json['PRODUCT']['SLIDER'] as $item){
            $link = 'https://corizza.by' . $item['SRC'];
            $links[] = $link;
        }

        return $links;
    }
}