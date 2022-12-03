<?php

/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/4
 * Time: 3:19 PM
 */

require_once 'Constants.php';

/**
 * 类自动加载
 * @param $class
 * @return void
 */
function classLoader($class)
{
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = getFile($class);
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('classLoader');


/**
 * 获取需要加载的类文件
 * @param string $class
 * @return string
 */
function getFile(string $class): string
{
    $namespace = explode(DIRECTORY_SEPARATOR, $class);
    $filePath = match ($namespace[0]) {
        'OSS' => getOssSdkPath(),
        default => UPLOAD_PLUGIN_PATH,
    };
    if ($filePath === UPLOAD_PLUGIN_PATH) {
        $class = str_replace('Typecho/Plugin/Upload/', '', $class);
    }
    return $filePath . $class . '.php';
}


/**
 * 获取OSS SDK的路径
 * @return string
 */
function getOssSdkPath(): string
{
    $path = SDK_PATH . DIRECTORY_SEPARATOR;
    $path .= SDK_OSS . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    return $path;
}
