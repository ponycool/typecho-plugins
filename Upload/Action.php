<?php

require_once 'Autoload.php';

use Typecho\Date;
use Typecho\Db;
use Typecho\Plugin\Upload\Database;
use Typecho\Plugin\Upload\Handle;
use Typecho\Plugin\Upload\Log;
use Typecho\Widget;
use Typecho\Common;
use Widget\ActionInterface;
use Typecho\Db\Exception as DBException;

/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/12/15
 * Time: 2:56 PM
 */
class Upload_Action extends Widget implements ActionInterface
{
    private Db $db;
    private Widget $options;
    private string $prefix;

    public function action()
    {
        $user = Widget::widget('Widget_User');
        $user->pass('administrator');

        $dbInstance = new Database();
        try {
            $this->setDb($dbInstance->getDb())
                ->setPrefix($this->getDb()->getPrefix())
                ->setOptions(Widget::widget('Widget_Options'));
        } catch (DBException $e) {
            $this->error('表单异常', $e->getCode());
        }

        $this->on($this->request->is('do=delete'))->delete();
        $this->on($this->request->is('do=sort'))->sort();

        $this->response->redirect($this->getOptions()->adminUrl);
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
     * @return Upload_Action
     */
    public function setDb(Db $db): Upload_Action
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return Widget
     */
    public function getOptions(): Widget
    {
        return $this->options;
    }

    /**
     * @param Widget $options
     * @return Upload_Action
     */
    public function setOptions(Widget $options): Upload_Action
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): Upload_Action
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 获取当前时间
     * @return string
     */
    public function getCurrTime(): string
    {
        $date = new Date();
        $date::setTimezoneOffset(8);
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 获取管理页面路径
     * @return string
     */
    public function getPageUrl(): string
    {
        return 'extending.php?panel=Upload%2FMedia.php';
    }

    /**
     * 删除媒体文件
     * @return void
     */
    public function delete(): void
    {
        $id = $this->request->filter('int')->getArray('id');
        if ($id) {
            try {
                $query = $this->getDb()->select()
                    ->from('table.media')
                    ->where('id = ?', $id);
                $media = $this->getDb()->fetchRow($query);
                if (is_null($media)) {
                    return;
                }

                $table = $this->getPrefix() . 'media';
                $sql = sprintf(
                    "update %s set `deleted` = 1,`deleted_at` = '%s' where `id` in (%s)",
                    $table,
                    $this->getCurrTime(),
                    implode(',', $id)
                );
                $affectedRows = $this->getDb()->query($sql, $this->getDb()::WRITE, $this->getDb()::UPDATE);
                // 删除对象存储中的文件
                $object = 'img/' . $media['file_url'];
                Handle::deleteObject($object);
                // 删除本地文件
                $file = UPLOAD_ROOT . $media['file_url'];
                if (file_exists($file)) {
                    unlink($file);
                }
            } catch (Exception $e) {
                Log::message('删除媒体文件时发生异常' . $e->getCode());
                $this->error('删除媒体文件时发生异常', $e->getCode());
            }
        }

        // 提示信息
        $this->widget('Widget_Notice')->set(($affectedRows ?? 0) ? _t('媒体文件已经删除') : _t('没有媒体文件被删除'), NULL,
            ($affectedRows ?? 0) ? 'success' : 'notice');

        // 转向原页
        $this->response->redirect(
            Common::url($this->getPageUrl(), $this->getOptions()->adminUrl)
        );
    }

    /**
     * 排序
     * @return void
     */
    public function sort(): void
    {
    }

    /**
     * 错误提示
     * @param string $msg
     * @param int|null $code
     * @return void
     */
    public function error(string $msg, ?int $code): void
    {
        if ($code !== 0) {
            $msg .= '，错误代码：' . $code;
        }
        // 错误提示
        $this->widget('Widget_Notice')->set(_t($msg));
        // 转向原页
        $this->response->redirect(
            Common::url($this->getPageUrl(), $this->getOptions()->adminUrl)
        );
    }
}