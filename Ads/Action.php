<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/1
 * Time: 2:40 PM
 */

require_once 'Form.php';

use Typecho\Common;
use Typecho\Date;
use Typecho\Db;
use Typecho\Widget;
use Widget\ActionInterface;
use Typecho\Plugin\Ads\Ads_Form;
use Typecho\Db\Exception as DBException;
use Typecho\Widget\Exception as WidgetException;

class Ads_Action extends Widget implements ActionInterface
{

    private Db $db;
    private Widget $options;
    private string $prefix;

    /**
     * @return Db
     */
    public function getDb(): Db
    {
        return $this->db;
    }

    /**
     * @param Db $db
     * @return Ads_Action
     */
    public function setDb(Db $db): Ads_Action
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
     * @return Ads_Action
     */
    public function setOptions(Widget $options): Ads_Action
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
     * @param mixed $prefix
     */
    public function setPrefix(string $prefix): Ads_Action
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
        return 'extending.php?panel=Ads%2FManageAds.php';
    }

    /**
     * @return void
     */
    public function action(): void
    {
        $user = Widget::widget('Widget_User');
        $user->pass('administrator');

        try {
            $this->setDb(Db::get())
                ->setPrefix($this->getDb()->getPrefix())
                ->setOptions(Widget::widget('Widget_Options'));
        } catch (DBException $e) {
            $this->error('表单异常', $e->getCode());
        }

        $this->on($this->request->is('do=insert'))->insert();
        $this->on($this->request->is('do=update'))->update();
        $this->on($this->request->is('do=delete'))->delete();
        $this->on($this->request->is('do=sort'))->sort();

        $this->response->redirect($this->getOptions()->adminUrl);
    }

    /**
     * 新增广告代码位
     * @return void
     */
    public function insert(): void
    {
        try {
            if (Ads_Form::form('insert')->validate()) {
                $this->response->goBack();
            }
        } catch (DBException|WidgetException $e) {
            $this->error('新增广告代码位验证表单时发生异常', $e->getCode());
        }

        $ad = $this->request->from('ad_name', 'ad_desc', 'ad_code', 'sort_index');
        $ad['created_at'] = $this->getCurrTime();

        try {
            $ad['id'] = $this->getDb()->query(
                $this->getDb()->insert($this->getPrefix() . 'ads')
                    ->rows($ad)
            );
        } catch (DBException $e) {
            $this->error('新增广告代码位时发生异常', $e->getCode());
        }

        // 设置高亮
        $this->widget('Widget_Notice')->highlight('ad-' . $ad['id']);

        // 提示信息
        $this->widget('Widget_Notice')->set(
            _t('广告代码位已增加'),
            null,
            'success'
        );

        $this->response->redirect(
            Common::url($this->getPageUrl(), $this->getOptions()->adminUrl)
        );
    }

    /**
     * 更新广告代码位
     * @return void
     */
    public function update(): void
    {
        try {
            if (Ads_Form::form('update')->validate()) {
                $this->response->goBack();
            }
        } catch (DBException|WidgetException $e) {
            $this->error('更新广告代码位时验证表单发生异常', $e->getCode());
        }

        $ad = $this->request->from('id', 'ad_name', 'ad_desc', 'ad_code', 'sort_index');
        $ad['updated_at'] = $this->getCurrTime();
        try {
            $this->getDb()->query(
                $this->getDb()->update($this->getPrefix() . 'ads')
                    ->rows($ad)
                    ->where('id = ?', $ad['id'])
            );
        } catch (DBException $e) {
            $this->error('更新广告代码位时发生异常', $e->getCode());
        }

        $this->widget('Widget_Notice')->highlight('ad-' . $ad['id']);

        $this->widget('Widget_Notice')->set(
            _t('广告代码位已被更新'),
            null,
            'success'
        );

        $this->response->redirect(
            Common::url($this->getPageUrl(), $this->getOptions()->adminUrl)
        );
    }

    /**
     * 删除广告代码位
     * @return void
     */
    public function delete(): void
    {
        $ids = $this->request->filter('int')->getArray('id');
        if ($ids) {
            try {
                $table = $this->getPrefix() . 'ads';
                $sql = sprintf(
                    "update %s set `deleted` = 1,`deleted_at` = '%s' where `id` in (%s)",
                    $table,
                    $this->getCurrTime(),
                    implode(',', $ids)
                );
                $affectedRows = $this->getDb()->query($sql, $this->getDb()::WRITE, $this->getDb()::UPDATE);
            } catch (Exception $e) {
                $this->error('删除广告代码位时发生异常', $e->getCode());
            }
        }

        // 提示信息
        $this->widget('Widget_Notice')->set(($affectedRows ?? 0) ? _t('广告代码位已经删除') : _t('没有广告代码位被删除'), NULL,
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
        $ids = $this->request->filter('int')->getArray('id');
        if ($ids) {
            $table = $this->getPrefix() . 'ads';
            $sql = sprintf("update %s set sort_index = case id ", $table);
            $count = 0;
            foreach ($ids as $id) {
                $count++;
                $sql .= sprintf("when %d then %d ", $id, $count);
            }
            $ids = implode(',', $ids);
            $sql .= sprintf("end, updated_at = '%s' ", $this->getCurrTime());
            $sql .= sprintf("where id in(%s)", $ids);
            try {
                $this->getDb()->query($sql, $this->getDb()::WRITE, $this->getDb()::UPDATE);
            } catch (DBException $e) {
                $this->error('更改排序索引失败', $e->getCode());
            }
        }
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