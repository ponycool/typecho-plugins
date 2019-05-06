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
        $enableStatus = new Typecho_Widget_Helper_Form_Element_Radio('enable_status', array('禁用', '开启'), 0, '禁用/开启：', '启用状态，启用后将获取HTTP_X_FORWARDED_FOR中客户端原始IP');
        $form->addInput($enableStatus);
        $proxyIps = new Typecho_Widget_Helper_Form_Element_Textarea('proxy_ips', null, null, '代理IP（选填）:', '代理IP会自动过滤，请谨慎填写！每行一个IP');
        $form->addInput($proxyIps);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    public function setRealIP()
    {
        $realIp = '0.0.0.0';
        $options = Helper::options();
        $pluginConfig = $options->plugin('GetRealIP');
        $proxyIpsStr = $pluginConfig->proxy_ips;
        $proxyIps = explode(PHP_EOL, $proxyIpsStr);
        $enableStatus = $pluginConfig->enable_status;
        if (!$enableStatus) {
            return;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($realList as $k => $v) {
                if (in_array($v, $proxyIps)) {
                    unset($realList[$k]);
                }
            }
            if (!is_null($realList[0])) {
                $realIp = $realList[0];
            }
            $_SERVER['REMOTE_ADDR'] = $realIp;
        }
    }
}