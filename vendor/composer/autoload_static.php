<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit82db6b4a3e14a2ec8d5e20ba9f93b6b5
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'Leivingson\\AutoDB\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Leivingson\\AutoDB\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit82db6b4a3e14a2ec8d5e20ba9f93b6b5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit82db6b4a3e14a2ec8d5e20ba9f93b6b5::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit82db6b4a3e14a2ec8d5e20ba9f93b6b5::$classMap;

        }, null, ClassLoader::class);
    }
}
