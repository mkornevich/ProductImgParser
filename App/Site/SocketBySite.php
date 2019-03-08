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

    public function getProductsBySearchQuery($query)
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

    public function getImgLinksByProductUrl($url)
    {
        $links = [];

        $document = HTTP::getPQDocument($url);

        $images = $document->find("div.product__slider div.product__slider-item img");

        foreach ($images as $img){
            $links[] = 'https://www.socket.by' . pq($img)->attr('src');
        }

        return $links;
    }
}