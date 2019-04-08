<?php

/**
 * GetRealIP
 * 如果您的服务器位于反向代理后面，开启本插件后可以获取客户端真实IP
 *
 * @package GetRealIP
 * @author Pony
 * @version 1.0.0
 * @link http://blog.ponycool.com
 */
class GetRealIP_Plugin implements Typecho_Plugin_Interface
{
    protected static $key = "plugin:GetRealIP";
    protected static $table = "table.options";

    /* 激活插件方法 */
    public static function activate()
    {
        echo '2222222';
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('GetRealIP_Plugin', 'setRealIP');
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
                    'value' => "on"
                ));
            $insertId = $db->query($insert);
        }
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
        $element = new Typecho_Widget_Helper_Form_Element_Textarea("代理IP",array("127.0.0.1"),1,2,3);
        $form->addInput($element);
    }

    /* 个人用户的配置方法 */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement personalConfig() method.
    }

    public function setRealIP()
    {
        echo '1111111';
    }
}