<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit43a9c1d41a2c7a7a5fd0caa7db0e2955
{
    public static $files = array (
        '76f3b3938a9242c624d7a7c2114523ea' => __DIR__ . '/../..' . '/Libs/phpQuery.php',
    );

    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit43a9c1d41a2c7a7a5fd0caa7db0e2955::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit43a9c1d41a2c7a7a5fd0caa7db0e2955::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
