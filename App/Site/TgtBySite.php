<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 18.03.2019
 * Time: 14:12
 */

namespace App\Site;


use App\Base\BaseSite;
use App\Module\HTTP;

class TgtBySite extends BaseSite
{

    public function getProductsBySearchQuery($query, $args = [])
    {
        $products = [];
        $document = HTTP::getPQDocument('https://tgt.by/search/?q=' . urlencode($query));
        $links = $document->find('div.catalog_block.items.block_list div.item-title a');
        foreach ($links as $link) {
            $products[] = [
                'url' => 'https://tgt.by' . pq($link)->attr('href')
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

        $images = $document->find("div.slides a img");

        foreach ($images as $img){
            $link = 'https://tgt.by' . pq($img)->attr('src');
            if(in_array($link, $excludeLinks)) continue;
            $links[] = $link;
        }



        return $links;
    }
}