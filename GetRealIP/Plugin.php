<?php

use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Plugin\Exception;

/**
 * 如果您的服务器位于反向代理后面，开启本插件后可以获取客户端真实IP
 *
 * @package GetRealIP
 * @author Pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 */
class GetRealIP_Plugin implements PluginInterface
{
    protected static string $pluginName = "GetRealIP";

    /* 激活插件方法 */
    public static function activate(): string
    {
        $method = "setRealIP";
        Typecho_Plugin::factory('index.php')->begin = array('GetRealIP_Plugin', $method);
        return '插件安装成功，请进入设置填写代理IP';
    }

    /* 禁用插件方法 */
    public static function deactivate(): string
    {
        return '插件卸载成功';
    }

    /* 插件配置方法 */
    public static function config(Form $form)
    {
        //保存接口调用地址
        $enableStatus = new Radio('enable_status', array('禁用', '开启'), 0, '禁用/开启：', '启用状态，启用后将获取HTTP_X_FORWARDED_FOR中客户端原始IP');
        $form->addInput($enableStatus);
        $proxyIps = new Textarea('proxy_ips', null, null, '代理IP（选填）:', '代理IP会自动过滤，请谨慎填写！每行一个IP');
        $form->addInput($proxyIps);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * @throws Exception
     */
    public static function setRealIP(): void
    {
        $realIp = '0.0.0.0';
        $options = Helper::options();
        try {
            $pluginConfig = $options->plugin('GetRealIP');
        } catch (Exception $exc) {
            throw new Exception('GetRealIP插件启用失败，错误代码：' . $exc->getCode());
        }
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