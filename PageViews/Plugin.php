<?php

/**
 * PageViews
 *
 * @package PageViews
 * @author Pony
 * @version 1.0.0
 * @link http://blog.ponycool.com
 */
class PageViews_Plugin implements Typecho_Plugin_Interface
{
    protected static $key = "plugin:PageViews";
    protected static $table = "table.options";

    /* 激活插件方法 */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('PageViews_Plugin', 'statisticalPageViews');
        //init
        $db = Typecho_Db::get();
        $query = $db->select('name', 'value')
            ->from(self::$table)
            ->where('name = ?', self::$key)
            ->where('user = ?', 0)
            ->limit(1);
        $result = $db->fetchRow($query);
        if (!isset($result[self::$key])) {
            $insert = $db->insert(self::$table)
                ->rows(array(
                    'name' => self::$key,
                    'user' => 0,
                    'value' => 0
                ));
            $insertId = $db->query($insert);
        }
    }

    /* 禁用插件方法 */
    public static function deactivate()
    {
        // TODO: Implement deactivate() method.
    }

    /* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement config() method.
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * 统计浏览量
     * @author Pony
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     */
    public static function statisticalPageViews()
    {
        if (Typecho_Widget::widget('Widget_Archive')->is('single')) {
            $db = Typecho_Db::get();
            $query = $db->select('name', 'value')
                ->from(self::$table)
                ->where('name = ?', self::$key)
                ->where('user = ?', 0)
                ->limit(1);
            $result = $db->fetchRow($query);
            //更新
            $pv = (int)$result['value'];
            $pv++;
            $update = $db->update(self::$table)
                ->rows(array(
                    'value' => $pv
                ))
                ->where('name = ?', self::$key);
            $updateRows = $db->query($update);
        }
    }

    /**
     * 显示浏览量
     * 语法: PageViews_Plugin::showPageViews();
     * 输出：'本站总访问量 XX 次'
     * 语法: PageViews_Plugin::showPageViews('点击量','次');
     * 输出：'点击量 XX 次'
     *
     * @author Pony
     * @param string $before
     * @param string $after
     * @throws Typecho_Db_Exception
     */
    public static function showPageViews($before = '本站总访问量 ', $after = ' 次')
    {
        $db = Typecho_Db::get();
        $query = $db->select('name', 'value')
            ->from(self::$table)
            ->where('name = ?', self::$key)
            ->where('user = ?', 0)
            ->limit(1);
        $result = $db->fetchRow($query);
        $res = $before . $result['value'] . $after;
        echo $res;
    }
}