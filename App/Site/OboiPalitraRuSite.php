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

class OboiPalitraRuSite extends BaseSite
{

    public function getProductsBySearchQuery($query, $args = [])
    {
        $products = [];
        $document = HTTP::getPQDocument('https://oboi-palitra.ru/search/?q=' . rawurlencode($query));
        $links = $document->find('ul.catalog_list > li > a');
        foreach ($links as $link) {
            $products[] = [
                'url' => 'https://oboi-palitra.ru' . pq($link)->attr('href')
            ];
        }
        return $products;
    }

    public function getImgLinksByProductUrl($url, $args = [])
    {
        $links = [];

        $document = HTTP::getPQDocument($url);

        // добавление главной картинки

        $mainImgStr = $document->find('div.wallpaper')->attr('style');


        preg_match_all('/^background-image: url\((.*)\)$/m', $mainImgStr, $matches, PREG_SET_ORDER, 0);
        $mainImgStr = 'https://oboi-palitra.ru' . $matches[0][1];


        $links[] = $mainImgStr;


        $images = $document->find("ul.texture_list > li > a");

        foreach ($images as $img){

            $links[] = 'https://oboi-palitra.ru' . pq($img)->attr('href');
        }



        return $links;
    }
}