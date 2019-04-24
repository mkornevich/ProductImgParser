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

class SintraUaSite extends BaseSite
{

    public function getProductsBySearchQuery($query, $args = [])
    {
        $products = [];
        $document = HTTP::getPQDocument('http://www.sintra.ua/component/search/?searchword=' . rawurlencode($query));
        $links = $document->find('dl.search-results dt.result-title a');
        foreach ($links as $link) {
            $products[] = [
                'url' => 'http://www.sintra.ua' . pq($link)->attr('href')
            ];
        }
        return $products;
    }

    public function getImgLinksByProductUrl($url, $args = [])
    {
        $links = [];
        $excludeLinks = [
//            'https://www.socket.by/bitrix/templates/new_designe/upload/no_photo.png',
//            'https://www.socket.by/upload/imager/58a9d36c75682fe43b0d5fc73f23a925.png',
        ];

        $document = HTTP::getPQDocument($url);

        $images = $document->find("ul.gal-slider li img");

        foreach ($images as $img){
            $link = 'http://www.sintra.ua' . pq($img)->attr('src');
            if(in_array($link, $excludeLinks)) continue;
            $links[] = $link;
        }



        return $links;
    }
}