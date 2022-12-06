<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/12/5
 * Time: 3:48 PM
 */

namespace Typecho\Plugin\Upload;

use Exception;
use Typecho\Db\Exception as DBException;

class Install
{
    /**
     *
     * @throws Exception
     */
    public static function init(): string
    {
        $dbInstance = new Database();
        $installScript = file_get_contents(sprintf('%s/%s.sql', DEPLOY_DIR, $dbInstance->getAdapter()));
        $installScript = $dbInstance->preScript($installScript);

        try {
            foreach ($installScript as $item) {
                $item = trim($item);
                if ($item) {
                    $dbInstance->getDb()->query($item, $dbInstance->getDb()::WRITE);
                }
            }
            return '创建文件上传媒体表，文件上传插件安装成功';
        } catch (DBException $e) {
            $code = $e->getCode();

            if (('mysql' === $dbInstance->getAdapter() && (1050 === $code || '42S01' === $code)) ||
                ('sqlite' === $dbInstance->getAdapter() && ('HY000' === $code || 1 === $code))) {
                try {
                    $table = $dbInstance->getTablePrefix() . 'media';
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
                    $dbInstance->getDb()->query($sql);
                    return '检测到文件上传媒体表，文件上传插件安装成功';
                } catch (DBException $exc) {
                    $code = $exc->getCode();
                    if (('mysql' === $dbInstance->getAdapter() && (1054 === $code || '42S22' === $code)) ||
                        ('sqlite' === $dbInstance->getAdapter() && ('HY000' === $code || 1 === $code))) {
                        return self::upgrade();
                    }
                    Log::message('媒体表检测失败，文件上传插件安装失败');
                    throw new Exception('媒体表检测失败，文件上传插件安装失败。错误代码：' . $e->getCode());
                }
            } else {
                Log::message('媒体表创建失败，文件上传插件安装失败');
                throw new Exception('媒体表创建失败，文件上传插件安装失败。错误代码：' . $e->getCode());
            }
        }
    }

    /**
     * 插件升级
     * @throws Exception
     */
    public static function upgrade(): string
    {
        $dbInstance = new Database();
        $script = file_get_contents(sprintf('%s/upgrade_%s.sql', DEPLOY_DIR, $dbInstance->getAdapter()));
        $script = $dbInstance->preScript($script);
        try {
            foreach ($script as $item) {
                $item = trim($item);
                if ($item) {
                    $dbInstance->getDb()->query($item, $dbInstance->getDb()::WRITE);
                }
            }
            return '检测到旧版本文件上传媒体表，文件上传插件升级成功';
        } catch (DBException $e) {
            $code = $e->getCode();
            if (('mysql' === $dbInstance->getAdapter() && (1060 === $code || '42s21' === $code))) {
                return '文件上传媒体表已存在，文件上传插件安装成功';
            }
            throw new Exception('文件上传插件安装失败。错误代码：' . $e->getCode());
        }
    }
}