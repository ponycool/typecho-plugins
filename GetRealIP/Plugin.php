<?php

/**
 * 如果您的服务器位于反向代理后面，开启本插件后可以获取客户端真实IP
 *
 * @package GetRealIP
 * @author Pony
 * @version 1.0.0
 * @link http://blog.ponycool.com
 */
class GetRealIP_Plugin implements Typecho_Plugin_Interface
{
    protected static $pluginName = "GetRealIP";

    /* 激活插件方法 */
    public static function activate()
    {
        $method = "setRealIP";
        Typecho_Plugin::factory('index.php')->begin = array('GetRealIP_Plugin', $method);
        return '插件安装成功，请进入设置填写代理IP';
    }

    /* 禁用插件方法 */
    public static function deactivate()
    {
        return '插件卸载成功';
    }

    /* 插件配置方法 */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        //保存接口调用地址
        $proxyIps = new Typecho_Widget_Helper_Form_Element_Textarea('proxy_ips', null, null, '代理IP:', '代理IP会自动过滤，请谨慎填写！每行一个IP');
        $form->addInput($proxyIps);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    public function setRealIP()
    {
        $options = Helper::options();
        $pluginConfig = $options->plugin('GetRealIP');
        $proxyIpsStr = $pluginConfig->proxy_ips;
        $proxyIps = explode(PHP_EOL, $proxyIpsStr);
        var_dump($proxyIps);
        //echo get_class($plugin_config);
//        $_SERVER['REMOTE_ADDR'] = "192.168.16.16";
//        define("__TYPECHO_IP_SOURCE__", "192.168.16.17");
//        echo __TYPECHO_IP_SOURCE__;
//        echo $_SERVER['REMOTE_ADDR'];
//        $_SERVER['__TYPECHO_IP_SOURCE__'] = "192.168.16.17";
//        print_r($_ENV);
//        // var_dump($_SERVER);
//        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//            $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//            $_SERVER['REMOTE_ADDR'] = $list[0];
//        }
    }
}