<?php

namespace App\Module;

final class IO{
    public static function writeLn(string $str = ''){
        self::write(PHP_EOL . $str);
    }

    public static function write(string $str){
        echo $str;
    }

    public static function clearDir($path, $delThisFolder = false){
        if (is_dir($path) === true)
        {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file)
            {
                self::clearDir(realpath($path) . '/' . $file, true);
            }

            if($delThisFolder) rmdir($path);
            return null;
        }

        else if (is_file($path) === true)
        {
            return unlink($path);
        }

        return false;
    }

}