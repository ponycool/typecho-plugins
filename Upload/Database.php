<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/12/5
 * Time: 3:57 PM
 */

namespace Typecho\Plugin\Upload;

use Typecho\Db;
use Typecho\Db\Exception as DBException;

class Database
{
    // 数据库适配器
    protected string $adapter;
    // 数据库实例
    protected Db $db;
    // 表前缀
    protected string $tablePrefix;

    public function __construct()
    {
        try {
            $db = Db::get();
        } catch (DBException $e) {
            $err = sprintf('安装%s插件时，获取数据库实例异常，错误代码：%s',
                PLUGIN_NAME,
                $e->getCode()
            );
            Log::message($err);
            return;
        }

        $this->setDb($db);

        $adapter = explode('_', $db->getAdapterName());
        $adapter = array_pop($adapter);
        $adapter = strtolower($adapter);

        $this->setAdapter($adapter);

        $prefix = $db->getPrefix();
        $this->setTablePrefix($prefix);
    }

    /**
     * @return string
     */
    public function getAdapter(): string
    {
        return $this->adapter;
    }

    /**
     * @param string $adapter
     */
    public function setAdapter(string $adapter): void
    {
        $this->adapter = $adapter;
    }

    /**
     * @return Db
     */
    public function getDb(): Db
    {
        return $this->db;
    }

    /**
     * @param Db $db
     */
    public function setDb(Db $db): void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * @param string $tablePrefix
     */
    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * 预处理脚本
     * @param string $script
     * @return array
     */
    public function preScript(string $script): array
    {
        $script = str_replace(DEFAULT_TABLE_PREFIX, $this->getTablePrefix(), $script);
        $script = str_replace('%charset%', 'utf8', $script);
        return explode(';', $script);
    }
}