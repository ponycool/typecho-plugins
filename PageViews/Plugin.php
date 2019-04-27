<?php

/**
 * 文章浏览量统计
 *
 * @package PageViews
 * @author Pony
 * @version 1.0.1
 * @link http://blog.ponycool.com
 */
class PageViews_Plugin implements Typecho_Plugin_Interface
{
    protected static $pluginName = "PageViews";

    /* 激活插件方法 */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('PageViews_Plugin', 'statisticalPageViews');
        return '插件安装成功，请进入设置填写起始浏览量';
    }

    /* 禁用插件方法 */
    public static function deactivate()
    {
        return '插件卸载成功';
    }

    /* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $views = new Typecho_Widget_Helper_Form_Element_Text("views", null, '0', '浏览量：', '文章起始浏览量，默认为0');
        $form->addInput($views);
        // TODO: Implement config() method.
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * 统计浏览量
     * @throws Typecho_Db_Exception
     * @throws Typecho_Exception
     * @author Pony
     */
    public static function statisticalPageViews()
    {
        if (Typecho_Widget::widget('Widget_Archive')->is('single')) {
            $options = Helper::options();
            $pluginConfig = $options->plugin(self::$pluginName);
            $views = $pluginConfig->views;
            $views++;
            //更新浏览量
            Helper::configPlugin(self::$pluginName, array('views' => $views));
        }
    }

    /**
     * 显示浏览量
     * 语法: PageViews_Plugin::showPageViews();
     * 输出：'本站总访问量 XX 次'
     * 语法: PageViews_Plugin::showPageViews('点击量','次');
     * 输出：'点击量 XX 次'
     *
     * @param string $before
     * @param string $after
     * @throws Typecho_Db_Exception
     * @author Pony
     */
    public static function showPageViews($before = '本站总访问量 ', $after = ' 次')
    {
        $options = Helper::options();
        $pluginConfig = $options->plugin(self::$pluginName);
        $views = $pluginConfig->views;
        $views++;
        $res = $before . $views . $after;
        echo $res;
    }
}