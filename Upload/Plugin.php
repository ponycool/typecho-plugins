<?php

/**
 * 文件上传插件
 * @package Upload
 * @author pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/4
 * Time: 11:02 AM
 */

require_once 'Autoload.php';

use Typecho\Plugin\Upload\Form as UploadForm;
use Typecho\Plugin\Upload\Install;
use Typecho\Widget;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Plugin\Upload\Handle;
use Utils\Helper;


class Upload_Plugin extends Widget implements PluginInterface
{
    public function api_version()
    {
        echo __FUNCTION__;
        var_dump(self::get_plugin_information()['version']);
    }

    public function api_log()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        } else {
        }
    }

    public static function get_plugin_information()
    {
        Typecho_Widget::widget('Widget_Plugins_List@activated', 'activated=1')->to($activatedPlugins);
        $activatedPlugins = json_decode(json_encode($activatedPlugins), true);
        $plugins_list = $activatedPlugins['stack'];
        $plugins_info = array();
        for ($i = 0; $i < count($plugins_list); $i++) {
            if ($plugins_list[$i]['title'] == 'AliOssForTypecho') {
                $plugins_info = $plugins_list[$i];
                break;
            }
        }
        if (count($plugins_info) < 1) {
            return false;
        }
        return $plugins_info;
    }


    /**
     * 激活插件
     * @return string
     * @throws Exception
     */
    public static function activate(): string
    {
        $initRes = Install::init();

        Typecho_Plugin::factory('Widget_Upload')->uploadHandle = [PLUGIN_NAME, 'uploadHandle'];
        Typecho_Plugin::factory('Widget_Upload')->modifyHandle = [PLUGIN_NAME, 'modifyHandle'];
        Typecho_Plugin::factory('Widget_Upload')->deleteHandle = [PLUGIN_NAME, 'deleteHandle'];
        Typecho_Plugin::factory('Widget_Upload')->attachmentHandle = [PLUGIN_NAME, 'attachmentHandle'];
        Typecho_Plugin::factory('Widget_Upload')->attachmentDataHandle = [PLUGIN_NAME, 'attachmentDataHandle'];

        Helper::addRoute('__alioss_for_tp_plugin_version__', '/__alioss_for_tp_plugin_api__/version', PLUGIN_NAME, 'api_version');
        Helper::addRoute('__alioss_for_tp_plugin_log__', '/__alioss_for_tp_plugin_api__/log', PLUGIN_NAME, 'api_log');

        return _t($initRes);
    }


    /**
     * 禁用插件
     * @return void
     */
    public static function deactivate(): void
    {
        Helper::removeRoute('__alioss_for_tp_plugin_version__');
        Helper::removeRoute('__alioss_for_tp_plugin_log__');
    }

    /**
     * 获取插件配置面板
     * @param Form $form
     * @return void
     */
    public static function config(Form $form): void
    {
        UploadForm::form($form);
    }

    /**
     * 个人用户的配置面板
     * @param Form $form
     * @return void
     */
    public static function personalConfig(Form $form): void
    {
    }

    /**
     * 上传文件处理函数
     *
     * @access public
     * @param array $file 上传的文件
     * @return array|bool
     */
    public static function uploadHandle(array $file)
    {
        return Handle::upload($file);
    }

    /**
     * 修改文件处理函数
     *
     * @access public
     * @param array $content 老文件
     * @param array $file 新上传的文件
     * @return array|bool
     */
    public static function modifyHandle(array $content, array $file)
    {
        return Handle::upload($file);
    }


    /**
     * 删除文件
     * @access public
     * @param array $content 文件相关信息
     * @return false
     */
    public static function deleteHandle(array $content)
    {
        return false;
    }

    /**
     * 获取实际文件绝对访问路径
     * @access public
     * @param array $content 文件相关信息
     * @return string
     */
    public static function attachmentHandle(array $content)
    {
        return Handle::attachment($content);
    }

    /**
     * 获取实际文件数据
     * @access public
     * @param array $content
     * @return string
     */
    public static function attachmentDataHandle(array $content)
    {
        return file_get_contents(self::attachmentHandle($content));
    }

}