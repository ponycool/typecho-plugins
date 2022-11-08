<?php

use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Db;
use Typecho\Widget\Helper\Form;
use Typecho\Db\Exception as DBException;
use Typecho\Plugin\Exception;

/**
 * 广告投放
 * @package Ads
 * @author Pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 */
class Ads_Plugin implements PluginInterface
{
    protected static string $pluginName = "Ads";
    // 部署脚本目录
    protected static string $deployDir = 'usr/plugins/Ads/deploy';
    // 数据库适配器
    protected static string $adapter;
    // 数据库实例
    protected static Db $db;
    // 表前缀
    protected static string $tablePrefix;

    /**
     * 激活插件
     * @return string
     * @throws Exception
     */
    public static function activate(): string
    {
        $installRes = Ads_Plugin::install();
        Helper::addPanel(3, 'Ads/ManageAds.php', '广告投放', '广告投放管理', 'administrator');
        Helper::addAction('ads-edit', 'Ads_Action');
        return _t($installRes);
    }

    /**
     * 禁用插件
     * @return void
     */
    public static function deactivate(): void
    {
        Helper::removeAction('ads-edit');
        Helper::removePanel(3, 'Ads/ManageAds.php');
    }

    /**
     * 获取插件配置面板
     * @param Form $form
     * @return void
     */
    public static function config(Form $form): void
    {
    }

    /**
     * 个人用户的配置面板
     * @param Form $form
     * @return void
     */
    public static function personalConfig(Form $form): void
    {
    }

    /**
     * @param string $adapter
     */
    public static function setAdapter(string $adapter): void
    {
        self::$adapter = $adapter;
    }


    /**
     * @param Db $db
     */
    public static function setDb(Db $db): void
    {
        self::$db = $db;
    }

    /**
     * @param string $tablePrefix
     */
    public static function setTablePrefix(string $tablePrefix): void
    {
        self::$tablePrefix = $tablePrefix;
    }

    /**
     * 插件安装
     * @throws Exception
     */
    public static function install(): string
    {
        try {
            $db = Db::get();
        } catch (DBException $exc) {
            return '安装Ads插件时，获取数据库实例异常，错误代码：' . $exc->getCode();
        }

        self::setDb($db);

        $adapter = explode('_', $db->getAdapterName());
        $adapter = array_pop($adapter);
        $adapter = strtolower($adapter);

        self::setAdapter($adapter);

        $prefix = self::$db->getPrefix();
        self::setTablePrefix($prefix);

        $installScript = file_get_contents(sprintf('%s/%s.sql', self::$deployDir, self::$adapter));
        $installScript = self::preScript($installScript);

        try {
            foreach ($installScript as $item) {
                $item = trim($item);
                if ($item) {
                    self::$db->query($item, $db::WRITE);
                }
            }
            return '创建广告投放数据表，插件安装成功';
        } catch (DBException $exc) {
            $code = $exc->getCode();

            if (('mysql' === self::$adapter && (1050 === $code || '42S01' === $code)) ||
                ('sqlite' === self::$adapter && ('HY000' === $code || 1 === $code))) {
                try {
                    $table = self::$tablePrefix . 'ads';
                    $fields = [
                        '`id`',
                        '`ad_name`',
                        '`ad_desc`',
                        '`ad_code`',
                        '`view_times`',
                        '`sort_index`',
                        '`created_at`',
                        '`updated_at`',
                        '`deleted_at`',
                        '`deleted`'
                    ];
                    $fields = implode(',', $fields);
                    $sql = sprintf('SELECT %s FROM %s', $fields, $table);
                    self::$db->query($sql);
                    return '检测到广告投放数据表，插件安装成功';
                } catch (DBException $exc) {
                    $code = $exc->getCode();
                    if (('mysql' === self::$adapter && (1054 === $code || '42S22' === $code)) ||
                        ('sqlite' === self::$adapter && ('HY000' === $code || 1 === $code))) {
                        return self::upgrade();
                    }
                    throw new Exception('数据表检测失败，广告投放插件安装失败。错误代码：' . $exc->getCode());
                }
            } else {
                throw new Exception('数据表创建失败，广告投放插件安装失败。错误代码：' . $exc->getCode());
            }
        }
    }

    /**
     * 插件升级
     * @throws Exception
     */
    public static function upgrade(): string
    {
        $script = file_get_contents(sprintf('%s/upgrade_%s.sql', self::$deployDir, self::$adapter));
        $script = self::preScript($script);
        try {
            foreach ($script as $item) {
                $item = trim($item);
                if ($item) {
                    self::$db->query($item, self::$db::WRITE);
                }
            }
            return '检测到旧版本广告投放数据表，插件升级成功';
        } catch (DBException $exc) {
            $code = $exc->getCode();
            if (('mysql' === self::$adapter && (1060 === $code || '42s21' === $code))) {
                return '广告投放数据表已存在，插件安装成功';
            }
            throw new Exception('广告投放插件安装失败。错误代码：' . $exc->getCode());
        }
    }

    /**
     * 预处理脚本
     * @param string $script
     * @return array
     */
    public static function preScript(string $script): array
    {
        $script = str_replace('typecho_', self::$tablePrefix, $script);
        $script = str_replace('%charset%', 'utf8', $script);
        return explode(';', $script);
    }

    /**
     * 显示广告代码位
     * @param int|null $id
     * @param string|null $adName
     * @return void
     */
    public static function show(?int $id = null, ?string $adName = null): void
    {
        try {
            $db = Db::get();

            self::setDb($db);
            self::setTablePrefix($db->getPrefix());
            $query = self::$db->select('`id`', '`view_times`', '`ad_code`')
                ->from(self::$tablePrefix . 'ads')
                ->where('`deleted` = 0');
            if (!is_null($id)) {
                $query->Where('`id` = ?', $id);
            }
            if (!is_null($adName)) {
                $query->Where('`ad_name` = ?', $adName);
            }
            $query->order('`sort_index`')
                ->order('`id`',)
                ->limit(1);
            $res = self::$db->fetchRow($query);

            // 回写展现次数
            $id = $res['id'];
            $viewTimes = (int)$res['view_times'];
            $viewTimes++;
            self::$db->query(
                self::$db->update(self::$tablePrefix . 'ads')
                    ->rows(['view_times' => $viewTimes])
                    ->where('id = ?', $id)
            );
            echo $res['ad_code'];
        } catch (DBException $e) {
            echo '获取广告代码位失败，错误代码：' . $e->getCode();
        }
    }
}