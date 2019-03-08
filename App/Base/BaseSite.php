<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 25.02.2019
 * Time: 20:18
 */

namespace App\Base;


abstract class BaseSite
{
    abstract public function getProductsBySearchQuery($query);

    abstract public function getImgLinksByProductUrl($url);
}