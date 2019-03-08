<?php
/**
 * Created by PhpStorm.
 * User: mkornevich
 * Date: 25.02.2019
 * Time: 20:41
 */

namespace App\Module;


final class HTTP
{
    public static function getPQDocument($url){
        $html = file_get_contents($url);

        libxml_use_internal_errors(true);
        $result = \phpQuery::newDocumentHTML($html);
        libxml_use_internal_errors(false);
        return $result;
    }

    public static function getJSONDocument($url){
        return json_decode(file_get_contents($url), true);
    }

    public static function saveImg($saveDir, $url){
        file_put_contents($saveDir, file_get_contents($url));
    }
}