<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/29
 * Time: 10:40 AM
 */

namespace Typecho\Plugin\Upload;

use Typecho\Widget;

class Conf
{
    protected string $objectStorage;
    protected string $userDir;
    protected string $bucketName;
    protected string $endPoint;
    protected string $accessID;
    protected string $accessKey;
    protected string $endPointSuffix;
    protected string $cdn;
    protected string $diyStyle;
    // 外网域名
    protected string $externalDomain;
    // 内网域名
    protected string $intranetDomain;
    protected bool $enableLocal;
    // 对象存储上的保存路径
    protected string $savePath;

    public function __construct()
    {
        $options = Widget::widget('Widget_Options');
        $plugin = $options->plugin('Upload');
        $endPoint = ($plugin->endPoint === "other") ?
            $plugin->otherEndPoint :
            'https://' . $plugin->endPoint . $this->getEndPointSuffix($plugin->objectStorage, $plugin->endPointType);

        // 对象存储上的保存路径
        $savePath = date('Y/m/d/');

        $scheme = 'https://';
        $externalDomain = sprintf('%s%s.%s%s',
            $scheme,
            $plugin->bucketName,
            $plugin->endPoint,
            ($plugin->endPoint === "other") ?
                $plugin->otherEndPoint :
                $this->getEndPointSuffix($plugin->objectStorage, 'external')
        );

        $intranetDomain = sprintf('%s%s.%s%s',
            $scheme,
            $plugin->bucketName,
            $plugin->endPoint,
            ($plugin->endPoint === "other") ?
                $plugin->otherEndPoint :
                $this->getEndPointSuffix($plugin->objectStorage, 'intranet')
        );

        $this->setObjectStorage($plugin->objectStorage)
            ->setUserDir($plugin->userDir)
            ->setBucketName($plugin->bucketName ?? '')
            ->setEndPoint($endPoint)
            ->setAccessID($plugin->accessKeyId ?? '')
            ->setAccessKey($plugin->accessKeySecret ?? '')
            ->setCdn($plugin->cdn)
            ->setDiyStyle($plugin->diyStyle)
            ->setExternalDomain($externalDomain)
            ->setIntranetDomain($intranetDomain)
            ->setEnableLocal($plugin->enableLocal)
            ->setSavePath($savePath);
    }

    /**
     * @return string
     */
    public function getObjectStorage(): string
    {
        return $this->objectStorage;
    }

    /**
     * @param string $objectStorage
     * @return Conf
     */
    public function setObjectStorage(string $objectStorage): Conf
    {
        $this->objectStorage = $objectStorage;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserDir(): string
    {
        return $this->userDir;
    }

    /**
     * @param string $userDir
     * @return Conf
     */
    public function setUserDir(string $userDir): Conf
    {
        $this->userDir = $userDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getBucketName(): string
    {
        return $this->bucketName;
    }

    /**
     * @param string $bucketName
     * @return Conf
     */
    public function setBucketName(string $bucketName): Conf
    {
        $this->bucketName = $bucketName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndPoint(): string
    {
        return $this->endPoint;
    }

    /**
     * @param string $endPoint
     * @return Conf
     */
    public function setEndPoint(string $endPoint): Conf
    {
        $this->endPoint = $endPoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessID(): string
    {
        return $this->accessID;
    }

    /**
     * @param string $accessID
     * @return Conf
     */
    public function setAccessID(string $accessID): Conf
    {
        $this->accessID = $accessID;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * @param string $accessKey
     * @return Conf
     */
    public function setAccessKey(string $accessKey): Conf
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    /**
     * 获取区域后缀
     * @param string $objectStorage
     * @param string $endPointType
     * @return string
     */
    public function getEndPointSuffix(string $objectStorage, string $endPointType): string
    {
        $this->endPointSuffix = match ($objectStorage) {
            'cos' => match ($endPointType) {
                'intranet', 'external' => '.myqcloud.com'
            },
            'obs' => match ($endPointType) {
                'intranet', 'external' => '.myhuaweicloud.com'
            },
            default => match ($endPointType) {
                'intranet' => '-internal.aliyuncs.com',
                'external' => '.aliyuncs.com'
            },
        };
        return $this->endPointSuffix;
    }

    /**
     * @param string $endPointSuffix
     * @return Conf
     */
    public function setEndPointSuffix(string $endPointSuffix): Conf
    {
        $this->endPointSuffix = $endPointSuffix;
        return $this;
    }

    /**
     * @return string
     */
    public function getCdn(): string
    {
        return $this->cdn;
    }

    /**
     * @param string $cdn
     * @return Conf
     */
    public function setCdn(string $cdn): Conf
    {
        $this->cdn = $cdn;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiyStyle(): string
    {
        return $this->diyStyle;
    }

    /**
     * @param string $diyStyle
     * @return Conf
     */
    public function setDiyStyle(string $diyStyle): Conf
    {
        $this->diyStyle = $diyStyle;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalDomain(): string
    {
        return $this->externalDomain;
    }

    /**
     * @param string $externalDomain
     * @return Conf
     */
    public function setExternalDomain(string $externalDomain): Conf
    {
        $this->externalDomain = $externalDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntranetDomain(): string
    {
        return $this->intranetDomain;
    }

    /**
     * @param string $intranetDomain
     * @return Conf
     */
    public function setIntranetDomain(string $intranetDomain): Conf
    {
        $this->intranetDomain = $intranetDomain;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnableLocal(): bool
    {
        return $this->enableLocal;
    }

    /**
     * @param bool $enableLocal
     * @return Conf
     */
    public function setEnableLocal(bool $enableLocal): Conf
    {
        $this->enableLocal = $enableLocal;
        return $this;
    }

    /**
     * @return string
     */
    public function getSavePath(): string
    {
        return $this->savePath;
    }

    /**
     * @param string $savePath
     * @return Conf
     */
    public function setSavePath(string $savePath): Conf
    {
        $this->savePath = $savePath;
        return $this;
    }
}