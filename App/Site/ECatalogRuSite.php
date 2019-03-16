<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 25.02.2019
 * Time: 22:20
 */

namespace App\Site;


use App\Base\BaseSite;
use App\Module\HTTP;

class ECatalogRuSite extends BaseSite
{

    public function getProductsBySearchQuery($query, $args = [])
    {
        $products = [];
        $document = HTTP::getPQDocument('https://www.e-katalog.ru/mtools/mui_qs3.php?input_dom_id_=&data_=' . rawurlencode($query));
        $links = $document->find('table tr a');
        foreach ($links as $link) {
            $products[] = [
                'url' => "https://www.e-katalog.ru" . pq($link)->attr('href'),
            ];
        }
        return $products;
    }

    public function getImgLinksByProductUrl($url, $args = [])
    {

        $imgLinks = [];

        $productHtml = HTTP::getPQDocument($url);

        // загружаем главную картинку
        $imgLinks[] = "https://www.e-katalog.ru" . $productHtml->find('div.img200 img[rel="v:photo"]')->attr('src');

        $productId = $productHtml->find('#menu_addto div.ib.toggle-off')->attr('id');
        $productId = mb_substr($productId, 2, mb_strlen($productId) - 2);

        $galleryResponse = file_get_contents('https://www.e-katalog.ru/mtools/mui_get_img_gallery.php?idg_=' . $productId . '&f_type_=IMG');

        if ($galleryResponse == '()') return $imgLinks;

        $galleryResponse = mb_substr($galleryResponse, 1, mb_strlen($galleryResponse) - 2); // убираем скобки

        $jsonGallery = json_decode($galleryResponse, true);

        if($jsonGallery['NumPhoto'] == '0') return$imgLinks;

        foreach ($jsonGallery['pp_images'] as $url){
            if($url[0] == '/')
                $imgLinks[] = 'https://www.e-katalog.ru' . $url;
            else
                $imgLinks[] = $url;
        }

        return $imgLinks;

    }
}