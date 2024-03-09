<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf91c868d0fa8c6cdf117913300995ed9
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'Nicolasflamel\\Secp256k1Zkp\\' => 27,
            'Nicolasflamel\\Blake2b\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Nicolasflamel\\Secp256k1Zkp\\' => 
        array (
            0 => __DIR__ . '/..' . '/nicolasflamel/secp256k1-zkp/src',
        ),
        'Nicolasflamel\\Blake2b\\' => 
        array (
            0 => __DIR__ . '/..' . '/nicolasflamel/blake2b/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf91c868d0fa8c6cdf117913300995ed9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf91c868d0fa8c6cdf117913300995ed9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf91c868d0fa8c6cdf117913300995ed9::$classMap;

        }, null, ClassLoader::class);
    }
}