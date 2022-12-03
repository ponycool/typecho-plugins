<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/29
 * Time: 3:20 PM
 */

namespace Typecho\Plugin\Upload\ObjectStorage;

use OSS\Core\OssException;
use OSS\OssClient;
use Typecho\Plugin\Upload\Log;

class Oss implements ObjectStorageInterface
{
    private ObjectStorage $os;

    public function __construct(ObjectStorage $objectStorage)
    {
        $this->os = $objectStorage;
    }

    /**
     * 上传文件
     * @param string $filePath 文件原始路径包含文件名称
     * @param string $osPath OSS保存路径包含文件名称
     * @return mixed OSS文件地址
     */
    public function upload(string $filePath, string $osPath): mixed
    {
        try {
            $ossClient = new OssClient($this->os->getAccessKey(), $this->os->getSecret(), $this->os->getRegion());
            $respResult = $ossClient->uploadFile($this->os->getBucket(), $osPath, $filePath);
            if ($respResult['info']['http_code'] !== 200) {
                Log::message(sprintf('OSS上传文件失败，error：%s', $respResult['body']));
                return false;
            }
            return $respResult;
        } catch (OssException $e) {
            Log::message(sprintf('OSS上传文件失败，error：%s', $e->getMessage()));
            return false;
        }
    }

    /**
     * 下载文件
     * @param string $file OS文件名包含OS文件夹
     * @param string $savePath OS保存路径包含文件名称
     * @return bool
     */
    public function download(string $file, string $savePath): bool
    {
        $options = [
            OssClient::OSS_FILE_DOWNLOAD => $savePath
        ];
        $ossClient = new OssClient($this->os->getAccessKey(), $this->os->getSecret(), $this->os->getRegion());
        $ossClient->getObject($this->os->getBucket(), $file, $options);
        return true;
    }


    /**
     * 检测Bucket是否存在
     * @return bool
     */
    public function bucketExist(): bool
    {
        $ossClient = new OssClient($this->os->getAccessKey(), $this->os->getSecret(), $this->os->getRegion());
        try {
            return $ossClient->doesBucketExist($this->os->getBucket());
        } catch (OssException $e) {
            Log::message(sprintf('OSS检测Bucket：%s时发生错误，error：%s', $this->os->getBucket(), $e->getMessage()));
            return false;
        }
    }

    /**
     * 检测对象是否存在
     * @param string $fileName
     * @return bool
     */
    public function objectExist(string $fileName): bool
    {
        $ossClient = new OssClient($this->os->getAccessKey(), $this->os->getSecret(), $this->os->getRegion());
        try {
            return $ossClient->doesObjectExist($this->os->getBucket(), $fileName);
        } catch (OssException $e) {
            Log::message(sprintf('OSS检测对象时发生错误，error：%s', $e->getMessage()));
            return false;
        }
    }
}