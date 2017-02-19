<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitac95db96554a8438c49165c5a8f336ba
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Petdiscount\\Api\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Petdiscount\\Api\\' => 
        array (
            0 => __DIR__ . '/..' . '/petdiscount/api-client/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitac95db96554a8438c49165c5a8f336ba::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitac95db96554a8438c49165c5a8f336ba::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
