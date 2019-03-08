<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 25.02.2019
 * Time: 18:49
 */

use App\Module\CSV;

require_once __DIR__ . "/vendor/autoload.php";


set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

function data($path){
    return __DIR__ . '/' . $path;
}

(new App\Main)->main();

//print_r($onlinerBy->getProductsBySearchQuery("xiaomi redmi"));
//print_r($onlinerBy->getImgLinksByProductUrl("https://catalog.onliner.by/mobile/xiaomi/mi8lite464gvbl"));






