<?php

use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Widget\Helper\Form\Element\Checkbox;
use Typecho\Plugin\Exception;

/**
 * 跨域资源共享(CORS) 是一种机制，它使用额外的 HTTP 头来告诉浏览器  让运行在一个 origin (domain) 上的Web应用被准许访问来自不同源服务器上的指定的资源。
 *
 * @package Cors
 * @author Pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 */
class Cors_Plugin implements PluginInterface
{
    protected static string $pluginName = "Cors";

    /* 激活插件方法 */
    public static function activate(): string
    {
        $method = "openCors";
        Typecho_Plugin::factory('index.php')->begin = array('Cors_Plugin', $method);
        return '插件安装成功，请进入设置填写授权跨域列表';
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
        $enableStatus = new Radio('enable_status', ['禁用', '开启'], 0, '禁用/开启：', '启用状态，启用后将开启跨域资源共享');
        $form->addInput($enableStatus);
        $origins = new Textarea('origins', null, null, '授权域名：', '跨域授权域名，请谨慎填写！多条规则需换行填写,单独填写星号（*）通配符，表示允许所有来源的跨域请求。例如：https://mayanpeng.cn');
        $form->addInput($origins);
        $methods = new Checkbox('methods', ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'], null, '授权方法：', '指定允许的跨域请求方法');
        $form->addInput($methods);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * 开启跨域资源共享
     * @throws Exception
     */
    public static function openCors(): void
    {
        $options = Helper::options();
        try {
            $pluginConfig = $options->plugin('Cors');
        } catch (Exception $exc) {
            throw new Exception('CORS插件启用失败，错误代码：' . $exc->getCode());
        }
        $originsStr = $pluginConfig->origins;
        $origins = explode(PHP_EOL, $originsStr);
        $methodsKeys = $pluginConfig->methods;
        $enableStatus = $pluginConfig->enable_status;
        if (!$enableStatus) {
            return;
        }
        $methods = array(
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'HEAD'
        );
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array('*', $origins)) {
            header('Access-Control-Allow-Origin: *');
        } else {
            if (in_array($origin, $origins)) {
                header('Access-Control-Allow-Origin: ' . $origin);
            }
        }
        if (is_null($methodsKeys)) {
            return;
        }
        $methods = array_intersect_key($methods, $methodsKeys);
        header('Access-Control-Allow-Methods:' . implode(',', $methods));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
    }
}