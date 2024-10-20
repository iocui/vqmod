<?php
/**
 * composer项目引用vqmod扩展初始化文件
 * see https://github.com/vqmod/vqmod/wiki  
 */

class VQModInit
{
    public static $version = '1.0.1';

    public static function load($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            // fix webman for window to file process/Monitor.php
            // see https://www.php.net/manual/zh/pcntl.constants.php
            !defined('SIGUSR1') && define('SIGUSR1', 30);
            !defined('SIGINT') && define('SIGINT', 2);
        }

        require_once(__DIR__ . '/source/vqmod.php');

        \VQMod::$modCache = 'runtime/_vqmod.mods';
        \VQMod::$checkedCache = 'runtime/_vqmod.checked';
        \VQMod::$vqCachePath = 'runtime/_vqmod.cache/';
        \VQMod::$logFolder = 'runtime/_vqmod.logs/';

        $path = dirname($file, 3);

        \VQMod::bootup($path); // root path

        return \VQMod::modCheck($file);
    }
}

