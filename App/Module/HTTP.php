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
    public static function get($url){

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);


        return file_get_contents($url, false, $context);
    }

    public static function getPQDocument($url){
        $html = self::get($url);

        libxml_use_internal_errors(true);
        $result = \phpQuery::newDocumentHTML($html);
        libxml_use_internal_errors(false);
        return $result;
    }

    public static function getJSONDocument($url){
        return json_decode(self::get($url), true);
    }

    public static function saveImg($saveDir, $url){
        file_put_contents($saveDir, self::get($url));
    }
}