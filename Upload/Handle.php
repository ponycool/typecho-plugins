<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/28
 * Time: 3:52 PM
 */

namespace Typecho\Plugin\Upload;

use Typecho\Plugin\Upload\ObjectStorage\ObjectStorage;
use Typecho\Plugin\Upload\ObjectStorage\ObjectStorageFactory;
use Typecho\Widget;

class Handle
{

    public static function upload($file): bool|array
    {
        self::fileCheck($file);

        return self::saveFile($file);
    }

    public static function attachment(array $content): string
    {
        $conf = new Conf();
        // todo 需要改成从数据表中获取
        if (empty($conf->getCdn())) {
            return sprintf('%s/%s%s%s',
                $conf->getExternalDomain(),
                $conf->getUserDir(),
                $content['attachment']->path,
                $conf->getDiyStyle()
            );
        }
        return sprintf('%s%s%s%s',
            $conf->getCdn(),
            $conf->getUserDir(),
            $content['attachment']->path,
            $conf->getDiyStyle()
        );
    }

    /**
     * 文件检查
     * @param $file
     * @return bool
     */
    public static function fileCheck($file): bool
    {
        if (empty($file['name'])) {
            return false;
        }

        $ext = self::getFileExtension($file['name']);
        if (!self::checkFileType($ext)) {
            return false;
        }

        if (($file['tmp_name'] ?? false) && ($file['bytes'] ?? false)) {
            return false;
        }

        return true;
    }

    /**
     * 获取文件扩展名
     * @param string $name
     * @return string
     */
    public static function getFileExtension(string $name): string
    {
        $info = pathinfo($name);
        return $info['extension'] ?? '';
    }

    /**
     * 检查文件扩展名是否在授权范围
     * @access private
     * @param string $ext 扩展名
     * @return boolean
     */
    private static function checkFileType(string $ext): bool
    {
        $options = Widget::widget('Widget_Options');
        $allowedFileTypes = $options->allowedAttachmentTypes;
        return in_array($ext, $allowedFileTypes);
    }

    /**
     * 返回文件内容
     * @param array $file
     * @return false|mixed|string
     */
    private static function getFileContent(array $file): mixed
    {
        $content = $file['bytes'];
        if (!$content) {
            $content = file_get_contents($file['tmp_name']);
        }
        return $content;
    }

    /**
     * 创建目录
     * @param string $path
     * @return bool
     */
    public static function makeDir(string $path): bool
    {
        $path = preg_replace("/\\\+/", '/', $path);
        $current = rtrim($path, '/');
        $last = $current;

        while (!is_dir($current) && str_contains($path, '/')) {
            $last = $current;
            $current = dirname($current);
        }

        if ($last == $current) {
            return true;
        }

        if (!@mkdir($last)) {
            return false;
        }

        $stat = @stat($last);
        $perms = $stat['mode'] & 0007777;
        @chmod($last, $perms);

        return self::makeDir($path);
    }

    /**
     * 获取对象存储
     * @param Conf $conf
     * @return object|null
     */
    protected static function getObjectStorage(Conf $conf): ?object
    {
        $objectStorage = new ObjectStorage();
        $objectStorage->setAccessKey($conf->getAccessID());
        $objectStorage->setSecret($conf->getAccessKey());
        $objectStorage->setBucket($conf->getBucketName());
        $objectStorage->setRegion($conf->getEndPoint());
        $factory = new ObjectStorageFactory();
        return $factory::factory($conf->getObjectStorage(), $objectStorage);
    }

    /**
     * 获取保存到对象存储的文件
     * @param Conf $conf
     * @param string $fileName
     * @return string
     */
    private static function getSavedOsFile(Conf $conf, string $fileName): string
    {
        return sprintf('%s%s',
            $conf->getUserDir() . $conf->getSavePath(),
            $fileName
        );
    }

    /**
     * 获取保存到本地的文件
     * @param Conf $conf
     * @param string $fileName
     * @return string
     */
    private static function getSaveLocalFile(Conf $conf, string $fileName): string
    {
        return sprintf('%s%s',
            UPLOAD_ROOT . $conf->getSavePath(),
            $fileName
        );
    }

    /**
     * 获取安全的文件名
     * @param $name
     * @return string
     */
    private static function getSafeName(&$name): string
    {
        $name = str_replace(array('"', '<', '>'), '', $name);
        $name = str_replace('\\', '/', $name);
        $name = !str_contains($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        $info = pathinfo($name);
        $name = substr($info['basename'], 1);
        return $name;
    }

    /**
     * 保存文件
     * @param array $file
     * @return bool|array
     */
    private static function saveFile(array $file): bool|array
    {
        $conf = new Conf();

        $safeFileName = self::getSafeName($file['name']);

        $saveOsFile = self::getSavedOsFile($conf, $safeFileName);
        $saveLocalFile = self::getSaveLocalFile($conf, $safeFileName);

        //获取文件Hash
        $fileContent = self::getFileContent($file);
        $hash = hash('md5', $fileContent);

        // todo 通过hash 判断是否上传过文件

        // 保存到本地

        $localSaveResult = self::saveFileToLocal($saveLocalFile, $fileContent);
        if ($localSaveResult === false) {
            Log::message('文件保存到服务器失败，请检查上传目录权限');
            return false;
        }

        // 获取文件的Hash
        $hashFile = self::getHashFile($saveLocalFile);
        $saveOsHashFile = self::getOsHashFile($saveOsFile, $hash);
        rename($saveLocalFile, $hashFile);

        // 上传OSS
        $ob = self::getObjectStorage($conf);
        if (!$ob->bucketExist()) {
            return false;
        }
        $uploadResult = $ob->upload($hashFile, $saveOsHashFile);

        if (is_null($uploadResult) || $uploadResult === false) {
            return false;
        }

        // 开启同步保留本地备份
        if (!$conf->isEnableLocal()) {
            unlink($hashFile);
        }

        // 渲染保存结果
        if (!is_array($uploadResult)) {
            Log::message('OSS上传结果格式异常');
            return false;
        }
        return self::uploadResult($uploadResult);
    }

    /**
     * 保存文件到本地
     * @param string $file
     * @param string $content
     * @return bool|int
     */
    private static function saveFileToLocal(string $file, string $content): bool|int
    {
        $localUploadDir = dirname($file);
        if (!is_dir($localUploadDir)) {
            self::makeDir($localUploadDir);
        }
        return file_put_contents($file, $content);
    }

    /**
     * 获取经过Hash计算后的文件
     * @param string $file
     * @return string
     */
    private static function getHashFile(string $file): string
    {
        $hash = hash_file('md5', $file);
        $pathInfo = pathinfo($file);
        return sprintf('%s%s%s.%s',
            $pathInfo['dirname'],
            DIRECTORY_SEPARATOR,
            $hash,
            $pathInfo['extension']
        );
    }

    /**
     * 获取经过Hsh计算后保存到对象存储的文件
     * @param string $file
     * @param string $hash
     * @return string
     */
    private static function getOsHashFile(string $file, string $hash): string
    {
        $pathInfo = pathinfo($file);
        return sprintf('%s%s%s.%s',
            $pathInfo['dirname'],
            DIRECTORY_SEPARATOR,
            $hash,
            $pathInfo['extension']
        );
    }

    private static function uploadResult(array $result): array
    {
        $conf = new Conf();
        $pathInfo = pathinfo($result['info']['url']);
        return array(
            'name' => $pathInfo['basename'],
            'path' => $conf->getSavePath() . $pathInfo['basename'],
            'size' => intval($result['info']['size_upload']),
            'type' => $pathInfo['extension'],
            'mime' => $result['oss-requestheaders']['Content-Type']
        );
    }
}