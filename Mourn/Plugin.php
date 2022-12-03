<?php

use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Plugin\Exception;

/**
 * 追悼模式插件
 * @package Mourn
 * @author Pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 */
class Mourn_Plugin implements PluginInterface
{
    protected static string $pluginName = "Mourn";

    /* 激活插件方法 */
    public static function activate(): string
    {
        $method = "mourn";
        Typecho_Plugin::factory('Widget_Archive')->afterRender = array('Mourn_Plugin', $method);
        return '插件安装成功';
    }

    /* 禁用插件方法 */
    public static function deactivate(): string
    {
        return '插件卸载成功';
    }

    /* 插件配置方法 */
    public static function config(Form $form)
    {
        $enableStatus = new Radio('enabled', ['禁用', '开启'], 0, '禁用/开启：', '启用状态，启用后将开启追悼模式，全站变灰');
        $form->addInput($enableStatus);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * 开启追悼模式
     */
    public static function mourn(): void
    {
        $options = Helper::options();
        try {
            $pluginConfig = $options->plugin(self::$pluginName);
        } catch (Exception) {
            return;
        }
        if ($pluginConfig->enabled === '1') {
            self::scriptRender();
        }
    }


    public static function scriptRender(): void
    {
        $script = <<< EOF
<script>
    window.onload = function () {
        (function () {
            let head,body,style;
            head=document.getElementsByTagName('head')[0]
            style=document.createElement("style")
            style.type="text/css"
            style.innerHTML=".mourn{-webkit-filter: grayscale(.95);}"
            head.appendChild(style)
            body = document.getElementsByTagName('body')[0]
            body.setAttribute("class","mourn"); 
        })();
    }
</script>
EOF;
        echo $script;
    }
}