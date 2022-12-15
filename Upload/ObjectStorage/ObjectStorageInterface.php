<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/29
 * Time: 3:20 PM
 */

namespace Typecho\Plugin\Upload\ObjectStorage;

interface ObjectStorageInterface
{
    /**
     * 初始化
     * ObjectStorageInterface constructor.
     * @param ObjectStorage $os
     */
    public function __construct(ObjectStorage $os);

    /**
     * 上传文件
     * @param string $filePath 文件原始路径包含文件名称
     * @param string $osPath OS保存路径包含文件名称
     * @return mixed OS文件地址
     */
    public function upload(string $filePath, string $osPath): mixed;

    /**
     * 下载文件
     * @param string $file OS文件名包含OS文件夹
     * @param string $savePath 保存路径，包含文件路径和文件名
     * @return bool
     */
    public function download(string $file, string $savePath): bool;

    /**
     * 检测Bucket是否存在
     * @return bool
     */
    public function bucketExist(): bool;

    /**
     * 检测对象是否存在
     * @param string $fileName
     * @return bool
     */
    public function objectExist(string $fileName): bool;

    /**
     * 删除对象
     * @param string $object
     * @return mixed
     */
    public function deleteObject(string $object): mixed;
}