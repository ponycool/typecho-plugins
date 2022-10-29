<?php

use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Plugin\Exception;

/**
 * 文章浏览量统计
 *
 * @package PageViews
 * @author Pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 */
class PageViews_Plugin implements PluginInterface
{
    protected static string $pluginName = "PageViews";

    /* 激活插件方法 */
    public static function activate(): string
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('PageViews_Plugin', 'statisticalPageViews');
        return '插件安装成功，请进入设置填写起始浏览量';
    }

    /* 禁用插件方法 */
    public static function deactivate(): string
    {
        return '插件卸载成功';
    }

    /* 插件配置方法 */
    public static function config(Form $form)
    {
        $prefix = new Text("prefix", null, '本站总访问量', '显示前缀：', '显示前缀');
        $suffix = new Text("suffix", null, '次', '统计单位：', '统计单位，默认为次');
        $views = new Text("views", null, '0', '浏览量：', '文章起始浏览量，默认为0');
        $isHumanize = new Radio("isHumanize", ['禁用', '开启'], 0, '易读模式：', '易读模式，使用（K、M、B）进行计数');
        $form->addInput($prefix);
        $form->addInput($suffix);
        $form->addInput($views);
        $form->addInput($isHumanize);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * 统计浏览量
     * @throws Exception
     * @author Pony
     */
    public static function statisticalPageViews()
    {
        if (Typecho_Widget::widget('Widget_Archive')->is('single')) {
            $options = Helper::options();
            try {
                $pluginConfig = $options->plugin(self::$pluginName);
            } catch (Exception $exc) {
                throw new Exception('PageViews插件启用失败，错误代码：' . $exc->getCode());
            }
            $views = $pluginConfig->views;
            $views++;
            //更新浏览量
            Helper::configPlugin(self::$pluginName, array('views' => $views));
        }
    }

    /**
     * 显示浏览量
     * 语法: PageViews_Plugin::showPageViews();
     * @throws Exception
     * @author Pony
     */
    public static function showPageViews()
    {
        $options = Helper::options();
        try {
            $pluginConfig = $options->plugin(self::$pluginName);
        } catch (Exception $exc) {
            throw new Exception('PageViews插件显示浏览量异常，错误代码：' . $exc->getCode());
        }
        $views = $pluginConfig->views;
        $prefix = $pluginConfig->prefix;
        $suffix = $pluginConfig->suffix;
        $isHumanize = $pluginConfig->isHumanize;
        $views++;
        if ($isHumanize === '1') {
            $views = self::humanize($views);
        }
        $res = sprintf('%s %s', $prefix, $views);
        if ($suffix !== null) {
            $res = sprintf('%s %s', $res, $suffix);
        }
        echo $res;
    }

    /**
     * 转换为人类易读格式
     * @param int $views
     * @return string
     */
    public static function humanize(int $views): string
    {
        return match (true) {
            $views > 100000000 => sprintf('%s.0fB+', $views / 100000000),
            $views > 1000000 => sprintf('%s.0fM+', $views / 1000000),
            $views > 1000 => sprintf('%.0fK+', $views / 1000),
            default => sprintf('%s', $views),
        };
    }
}