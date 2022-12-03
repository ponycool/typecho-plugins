<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/29
 * Time: 3:17 PM
 */

namespace Typecho\Plugin\Upload\ObjectStorage;

use Exception;
use Typecho\Plugin\Upload\Log;

class ObjectStorage
{
    protected string $access_key;
    protected string $secret;
    protected string $region;
    protected string $bucket;
    protected ?string $domain;

    public function __construct()
    {
        $this->setDomain(null);
    }

    /**
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->access_key;
    }

    /**
     * @param string $access_key
     */
    public function setAccessKey(string $access_key): void
    {
        $this->access_key = $access_key;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * @param string $bucket
     */
    public function setBucket(string $bucket): void
    {
        $this->bucket = $bucket;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * 检查配置
     * @return bool
     */
    public function check(): bool
    {
        try {
            if (strlen($this->getAccessKey()) === 0) {
                throw new Exception('未配置有效的AccessKey');
            }
            if (strlen($this->getSecret()) === 0) {
                throw new Exception('未配置有效的Secret');
            }
            if (strlen($this->getRegion()) === 0) {
                throw new Exception('未配置有效的Region或Endpoint');
            }
            if (strlen($this->getBucket()) === 0) {
                throw new Exception('未配置有效的Bucket');
            }
            return true;
        } catch (Exception $e) {
            Log::message(sprintf('Object Storage 配置无效，error：%s', $e->getMessage()));
            return false;
        }
    }
}