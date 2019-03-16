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

class SocketBySite extends BaseSite
{

    public function getProductsBySearchQuery($query, $args = [])
    {
        $products = [];
        $document = HTTP::getPQDocument('https://www.socket.by/search/?q=' . rawurlencode($query));
        $links = $document->find('div.found-sections-product div.product-card.product-card-row div.title a');
        foreach ($links as $link) {
            $products[] = [
                'url' => 'https://www.socket.by' . pq($link)->attr('href')
            ];
        }
        return $products;
    }

    public function getImgLinksByProductUrl($url, $args = [])
    {
        $links = [];
        $excludeLinks = [
            'https://www.socket.by/bitrix/templates/new_designe/upload/no_photo.png',
            'https://www.socket.by/upload/imager/58a9d36c75682fe43b0d5fc73f23a925.png',
        ];

        $document = HTTP::getPQDocument($url);

        $images = $document->find("div.product__slider div.product__slider-item img");

        foreach ($images as $img){
            $link = 'https://www.socket.by' . pq($img)->attr('src');
            if(in_array($link, $excludeLinks)) continue;
            $links[] = $link;
        }



        return $links;
    }
}