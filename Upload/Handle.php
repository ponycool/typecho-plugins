<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/28
 * Time: 3:52 PM
 */

namespace Typecho\Plugin\Upload;

use Typecho\Db\Exception;
use Typecho\Plugin\Upload\ObjectStorage\ObjectStorage;
use Typecho\Plugin\Upload\ObjectStorage\ObjectStorageFactory;
use Typecho\Widget;

class Handle
{

    /**
     * 上传
     * @param $file
     * @return bool|array
     */
    public static function upload($file): bool|array
    {
        self::fileCheck($file);

        return self::saveFile($file);
    }

    /**
     * 附件
     * @param array $content
     * @return string
     */
    public static function attachment(array $content): string
    {
        $cdnUrl = $content['attachment']['cdn_url'] ?? null;
        if (empty($cdnUrl)) {
            return $content['attachment']['external_url'] ?? '';
        }
        return $cdnUrl;
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
     * 删除操作
     * @param array $content
     * @return bool
     */
    public static function delete(array $content): bool
    {
        $text = $content['text'];
        $image = self::getImageByPreg($text);
        if (is_null($image)) {
            return false;
        }

        $pathInfo = pathinfo($image);
        $md5 = $pathInfo['filename'];
        $dbInstance = new Database();
        try {
            $query = $dbInstance->getDb()->select()
                ->from('table.media')
                ->where('md5 = ?', $md5);
            $result = $dbInstance->getDb()->fetchRow($query);
            if (is_null($result)) {
                return false;
            }
            // 删除本地文件
            $file = UPLOAD_ROOT . $result['file_url'];
            if (file_exists($file)) {
                unlink($file);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::message("删除附件时发生异常，错误代码：" . $e->getCode());
        }
        return false;
    }

    /**
     * 删除对象存储中的对象
     * @param string $object
     * @return mixed
     */
    public static function deleteObject(string $object): mixed
    {
        $conf = new Conf();
        // 删除对象
        $ob = self::getObjectStorage($conf);
        if (!$ob->bucketExist()) {
            return false;
        }
        return $ob->deleteObject($object);
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
        $hash_md5 = hash('md5', $fileContent);
        $hash = hash('sha512', $fileContent);

        // 通过hash 判断是否上传过文件
        $media = self::getMediaByHash($hash);
        if (!is_null($media)) {
            $media['name'] = $media['file_name'];
            $media['type'] = $media['ext'];
            $media['path'] = $media['file_url'];
            return $media;
        }

        // 保存到本地
        $localSaveResult = self::saveFileToLocal($saveLocalFile, $fileContent);
        if ($localSaveResult === false) {
            Log::message('文件保存到服务器失败，请检查上传目录权限');
            return false;
        }

        // 获取文件的Hash
        $hashFile = self::getHashFile($saveLocalFile);
        $saveOsHashFile = self::getOsHashFile($saveOsFile, $hash_md5);
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

        // 保存到媒体表中
        $fileInfo = pathinfo($hashFile);
        $fileName = $fileInfo['basename'];
        $size = $uploadResult['info']['size_upload'];
        $media = [
            'file_name' => $fileName,
            'external_url' => self::externalUrl($fileName),
            'intranet_url' => self::intranetUrl($fileName),
            'cdn_url' => self::cdnUrl($fileName),
            'size' => $size,
            'ext' => $fileInfo['extension'],
            'md5' => $hash_md5,
            'hash' => $hash,
            'mime' => $uploadResult['oss-requestheaders']['Content-Type']
        ];
        self::saveMedia($media);

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

    /**
     * 上传结果
     * @param array $result
     * @return array
     */
    private static function uploadResult(array $result): array
    {
        $conf = new Conf();
        $pathInfo = pathinfo($result['info']['url']);
        return [
            'name' => $pathInfo['basename'],
            'path' => $conf->getSavePath() . $pathInfo['basename'],
            'size' => intval($result['info']['size_upload']),
            'type' => $pathInfo['extension'],
            'mime' => $result['oss-requestheaders']['Content-Type']
        ];
    }

    /**
     * 根据文件Hash获取媒体文件
     * @param string $hash
     * @return array|null
     */
    private static function getMediaByHash(string $hash): ?array
    {
        $dbInstance = new Database();
        $table = $dbInstance->getTablePrefix() . 'media';
        try {
            return $dbInstance->getDb()->fetchRow(
                $dbInstance->getDb()->select()
                    ->from($table)
                    ->where('hash = ?', $hash)
                    ->where('deleted = ?', 0)
                    ->limit(1)
            );
        } catch (Exception $e) {
            Log::message('根据hash获取媒体发生异常，错误代码：' . $e->getCode());
            return null;
        }
    }

    /**
     * 保存到媒体表
     * @param array $media
     * @return void
     */
    private static function saveMedia(array $media): void
    {
        $conf = new Conf();
        $time = date('Y-m-d H:i:s');
        $media['file_url'] = $conf->getSavePath() . $media['file_name'];
        $media['hash_alg'] = 'sha512';
        $media['object_storage'] = $conf->getObjectStorage();
        $media['type'] = 'image';
        $media['status'] = 'normal';
        $media['created_at'] = $time;
        $media['deleted'] = 0;
        $media['size_by_unit'] = self::getSizeByUnit($media['size'], 'MB');
        $dbInstance = new Database();
        $table = $dbInstance->getTablePrefix() . 'media';
        try {
            $dbInstance->getDb()->query(
                $dbInstance->getDb()->insert($table)
                    ->rows($media)
                    ->expression('server_replica', (int)$conf->isEnableLocal())
                    ->expression('deleted', 0)
            );

        } catch (Exception $e) {
            Log::message('保存媒体数据发生异常，错误代码：' . $e->getCode());
        }
    }

    /**
     * 获取文件外网URL
     * @param string $fileName
     * @return string
     */
    private static function externalUrl(string $fileName): string
    {
        $conf = new Conf();
        return sprintf('%s/%s%s%s%s',
            $conf->getExternalDomain(),
            $conf->getUserDir(),
            $conf->getSavePath(),
            $fileName,
            $conf->getDiyStyle()
        );
    }

    /**
     * 获取文件内网URL
     * @param string $fileName
     * @return string
     */
    private static function intranetUrl(string $fileName): string
    {
        $conf = new Conf();
        return sprintf('%s/%s%s%s%s',
            $conf->getIntranetDomain(),
            $conf->getUserDir(),
            $conf->getSavePath(),
            $fileName,
            $conf->getDiyStyle()
        );
    }

    /**
     * 获取文件CDN URL
     * @param string $fileName
     * @return string
     */
    private static function cdnUrl(string $fileName): string
    {
        $conf = new Conf();
        if (strlen($conf->getCdn()) === 0) {
            return '';
        }
        return sprintf('%s/%s%s%s%s',
            $conf->getCdn(),
            $conf->getUserDir(),
            $conf->getSavePath(),
            $fileName,
            $conf->getDiyStyle()
        );
    }

    /**
     * 获取带单位的文件大小
     * @param int $size
     * @param string $unit
     * @return string
     */
    private static function getSizeByUnit(int $size, string $unit = 'b'): string
    {
        return match (strtolower($unit)) {
            'kb' => number_format($size / 1024, 3),
            'mb' => number_format(($size / 1024) / 1024, 3),
            default => $size,
        };
    }

    /**
     * 通过正则获取文本中的图片
     * @param string $str
     * @return string|null
     */
    private static function getImageByPreg(string $str): ?string
    {
        $image_pattern = "/<img.*?src=['|\"](.*?)['|\"].*?\/?>/";
        preg_match_all($image_pattern, $str, $matches);
        if (!empty($matches[1])) {
            //循环匹配到的src
            foreach ($matches[1] as $src) {
                $src_real = strtok($src, '?'); //分割，去掉请求参数
                $ext = pathinfo($src_real, PATHINFO_EXTENSION); //获取拓展名
                if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                    return $src_real;
                }
            }
        }
        return null;
    }
}