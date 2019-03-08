<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 25.02.2019
 * Time: 22:19
 */

namespace App\Site;


use App\Base\BaseSite;
use App\Module\HTTP;

class OnlinerBySite extends BaseSite
{

    public function getProductsBySearchQuery($query)
    {
        $products = [];

        $jsonProducts = HTTP::getJSONDocument("https://catalog.api.onliner.by/search/products?query=" . rawurlencode($query));

        foreach ($jsonProducts['products'] as $jsonProduct){
            $products[] = [
                'url' => $jsonProduct['html_url'],
            ];
        }

        return $products;
    }

    public function getImgLinksByProductUrl($url)
    {
        $imgLinks = [];
        $productPage = HTTP::getPQDocument($url);

        $imgLinks[] = $productPage->find('#device-header-image')->attr('src');

        $imgBlocks = $productPage->find('div.product-gallery__shaft div.product-gallery__thumb[data-original]');

        foreach ($imgBlocks as $imgBlock){
            $imgLinks[] = pq($imgBlock)->attr('data-original');
        }

        return $imgLinks;
    }
}