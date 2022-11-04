<?php

use Typecho\Date;
use Utils\Helper;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Select;

/**
 * 运行时间显示
 * @package RunTime
 * @author pony
 * @version 1.2.0
 * @link https://mayanpeng.com
 */
class RunTime_Plugin implements PluginInterface
{

    public static string $pluginName = 'RunTime';

    /**
     * 激活插件
     * @return string
     */
    public static function activate(): string
    {
        return 'RunTime插件安装成功，请进入设置填写起始站点创建时间';
    }

    /**
     * 禁用插件
     * @return string
     */
    public static function deactivate(): string
    {
        return 'RunTime插件卸载成功';
    }

    /**
     * 插件配置
     * @param Form $form
     * @return void
     */
    public static function config(Form $form): void
    {
        self::formRender($form);
    }

    /**
     * 个人用户设置
     * @param Form $form
     * @return void
     */
    public static function personalConfig(Form $form): void
    {
        // TODO: Implement personalConfig() method.
    }

    /**
     * 表单渲染
     * @param Form $form
     * @return void
     */
    public static function formRender(Form $form): void
    {
        $isEnabled = new Radio("isEnabled", ['禁用', '开启'], 0, '是否开启运行时间：');
        $createdAt = new Text("createdAt", null, null, '站点创建时间：', '格式：2018/12/11 00:00:00');
        $prefix = new Text("prefix", null, null, '显示前缀：',
            '显示前缀，例："站点已安全运行",将显示为"站点已安全运行：x天 x小时 x分 x秒"'
        );
        $formatOptions = [
            0 => 'x天 x小时 x分 x秒 ',
            1 => 'x天',
            2 => 'xD xH xxM xxS',
        ];
        $format = new Select("format", $formatOptions, 0, '显示格式：', '显示格式，默认格式：X天X小时X分X秒');

        $form->addInput($isEnabled);
        $form->addInput($createdAt);
        $form->addInput($prefix);
        $form->addInput($format);
    }

    /**
     * 显示运行时间
     * @return void
     * @throws Exception
     */
    public static function show(): void
    {
        $options = Helper::options();
        try {
            $pluginConfig = $options->plugin(self::$pluginName);
        } catch (Exception $exc) {
            throw new Exception('RunTime插件异常，错误代码：' . $exc->getCode());
        }

        $isEnabled = $pluginConfig->isEnabled;
        if ((string)$isEnabled === '0') {
            return;
        }
        $createdAt = $pluginConfig->createdAt;

        if (empty($createdAt)) {
            $currTime = function () {
                $date = new Date();
                $date::setTimezoneOffset(8);
                return $date->format('Y/m/d H:i:s');
            };

            $createdAt = $currTime();
        }
        $prefix = $pluginConfig->prefix;
        $format = (int)$pluginConfig->format;

        match ($format) {
            1 => $runTime = 'day + "天"',
            2 => $runTime = 'day + "D " + hour + "H " + minute + "M " + second + "S"',
            default => $runTime = 'day + "天" + hour + "小时" + minute + "分" + second + "秒"'
        };

        if ($prefix) {
            $runTime = sprintf("'%s：'+%s", $prefix, $runTime);
        }

        $script = <<<EOF
<!-- 运行时间 Start -->
<span id="website_runtime"></span>
<script type="text/javascript">
    //站点运行时间
    function show_runtime() {
        // 定时器
        window.setTimeout("show_runtime()", 1000);
        // 开始时间
        let start = new Date('$createdAt');
        // 当前时间
        let now = new Date();
        // 经过时长
        let duration = now.getTime() - start.getTime();
        // 一天的毫秒数
        let msPerDay = 24 * 60 * 60 * 1000;
        // 天
        let _day = duration / msPerDay;
        let day = Math.floor(_day);
        // 小时
        let _hour = (_day - day) * 24;
        let hour = Math.floor(_hour);
        // 分钟
        let _minute = (_hour - hour) * 60;
        let minute = Math.floor(_minute);
        // 秒
        let _second = (_minute - minute) * 60;
        let second = Math.floor(_second);
        // 更新span标签内容
        let run_text=document.getElementById('website_runtime');
        run_text.innerHTML = $runTime;
    }

    show_runtime();
</script>
<!-- 运行时间 End -->
EOF;
        echo $script;
    }
}