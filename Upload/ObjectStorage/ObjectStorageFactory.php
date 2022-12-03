<?php

/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/29
 * Time: 10:59 AM
 */

namespace Typecho\Plugin\Upload\ObjectStorage;

use Exception;
use ReflectionClass;
use ReflectionException;
use Typecho\Plugin\Upload\Log;

class ObjectStorageFactory
{

    public static function factory(string $source, ObjectStorage $objectStorage): ?object
    {
        try {
            if ($objectStorage->check() !== true) {
                throw new Exception('Object Storage 配置无效');
            }
            $os = new ReflectionClass(__NAMESPACE__ . '\\' . ucfirst($source));
            if (!$os->isSubclassOf(__NAMESPACE__ . '\\ObjectStorageInterface')) {
                throw new ReflectionException($source . "未实现ObjectStorage接口类");
            }
            return $os->newInstance($objectStorage);
        } catch (ReflectionException|Exception $e) {
            Log::message(sprintf('%s加载失败，error：%s', $source, $e->getMessage()));
            return null;
        }
    }

}