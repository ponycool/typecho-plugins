<?php

/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/26
 * Time: 3:13 PM
 */

namespace Typecho\Plugin\Upload;

use Typecho\Widget\Helper\Form as BaseForm;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Select;
use Typecho\Widget\Helper\Form\Element\Radio;

class Form
{
    protected static array $endpoint;

    /**
     * 获取对象存储的区域
     * @param string $objectStorage
     * @return array
     */
    public static function getEndpoint(string $objectStorage = 'OSS'): array
    {
        $endpoint = match ($objectStorage) {
            'COS' => COS_ENDPOINT,
            'OBS' => OBS_ENDPOINT,
            default => OSS_ENDPOINT
        };
        $endpoint['other'] = '自定义';
        self::$endpoint = $endpoint;
        return self::$endpoint;
    }


    /**
     * 配置表单
     * @param BaseForm $form
     * @return void
     */
    public static function form(BaseForm $form): void
    {
        self::styleRender();
        self::menuRender();

        $objectStorage = new Select('objectStorage', OBJECT_STORAGE, 'OSS', _t('对象存储'),
            '默认为OSS，暂时只支持OSS，后期会增加其他对象存储的支持');
        $form->addInput($objectStorage);

        $buketName = new Text('bucketName', NULL, null,
            _t('Bucket名称'), _t('请填写Buket名称'));
        $form->addInput($buketName->addRule('required', _t('必须填写Bucket名称')));

        $accessKeyId = new Text('accessKeyId', NULL, null,
            _t('ACCESS KEY ID'), _t('请填写ACCESS KEY ID'));
        $form->addInput($accessKeyId->addRule('required', _t('必须填写ACCESS KEY ID')));

        $accessKeySecret = new Text('accessKeySecret', NULL, null,
            _t('ACCESS KEY SECRET'), _t('请填写请填写ACCESS KEY SECRET'));
        $form->addInput($accessKeySecret->addRule('required', _t('必须填写ACCESS_KEY')));

        $endPoint = new Select('endPoint',
            self::getEndpoint(),
            'oss-cn-qingdao',
            _t('区域选择（若区域不在列表中则选择自定义，然后填写区域）'), '');
        $form->addInput($endPoint);

        $endPointType = new Select('endPointType',
            ENDPOINT_TYPE,
            'external', '<label class="upload-mark-other-endpoint-hide">选择访问端口</label>',
            '<span class="upload-mark-other-endpoint-hide">在你了解两种连接方式的不同作用的情况下修改此选项</span>');
        $form->addInput($endPointType);

        $otherEndPoint = new Text('otherEndPoint', NULL, '',
            '<label class="upload-mark-other-endpoint-show">自定义EndPoint</label>', '<span class="upload-mark-other-endpoint-show">
            填写全部Endpoint地址，通常以\'.aliyuncs.com\'或\'-internal.aliyuncs.com\'结尾。开头不包含http://，结尾不包含"/"。<br/>例如"oss-cn-qingdao.aliyuncs.com"</span>');
        $form->addInput($otherEndPoint);

        $userDir = new Text('userDir', NULL, 'img/',
            _t('要储存的路径'), _t('请填写文件储存的路径（相对Bucket根目录），以字母或数字开头，以"/"结尾。留空则上传到根目录。'));
        $form->addInput($userDir);

        $cdn = new Text('cdn', NULL, '',
            _t('自定义（CDN）域名'), '请填写自定义域名，留空则使用外网Endpoint访问，以http://或https://开头，结尾请勿添加"/"');
        $form->addInput($cdn);

        $doc = '</br><a target="_blank" href="https://help.aliyun.com/document_detail/48884.html">阿里云文档</a>';
        $diyStyle = new Text('diyStyle', NULL, '', _t('默认自定义样式'),
            _t('通过后缀的方式使用自定义样式，留空为不使用。使用详情见' . $doc));
        $form->addInput($diyStyle);

        $form->addInput(new Radio('enableLocal', array("1" => '保留', "0" => '不保留'), "1",
            _t('在服务器保留备份'), _t('是否在服务器保留备份')));


        self::logRender();
        self::scriptRender();
    }

    /**
     * 渲染菜单
     * @return void
     */
    public static function menuRender(): void
    {
        $menu = <<< EOF
            <ul class="typecho-option-tabs clearfix">
                <li class="conf-menu current">
                    <a>
                        文件上传配置
                    </a>
                </li>
                <li class="log-menu">
                    <a>
                        错误日志
                    </a>
                </li>
                <li>
                    <a href="https://mayanpeng.cn/archives/219.html" title="查看广告投放使用帮助" target="_blank">
                        帮助
                    </a>
                </li>
            </ul>
EOF;
        echo $menu;
    }

    /**
     * 渲染样式
     * @return void
     */
    public static function styleRender(): void
    {
        $style = <<< EOF
<style type="text/css">
    .typecho-option-tabs>li:hover{
        cursor: pointer;  
    }
</style>
EOF;
        echo $style;
    }

    /**
     * 渲染脚本
     * @return void
     */
    public static function scriptRender(): void
    {
        $script = <<< EOF
        <script>
            window.onload = function () {
                (function () {
                    let upload_otherSelected = document.getElementsByName("endPoint")[0].value === "other";
                    const upload_otherEndpointShowingTags = document.getElementsByClassName("upload-mark-other-endpoint-show");
                    const upload_otherEndpointHidingTags = document.getElementsByClassName("upload-mark-other-endpoint-hide");
                    const upload_otherEndPointInputTag = document.getElementsByName("otherEndPoint")[0];
                    const upload_endPointTypeInputTag = document.getElementsByName("endPointType")[0];
                    const upload_loadLabels = function () {
                        let i  ,s1 , s2 ;
                        if (upload_otherSelected) {
                            s1 = "none";
                            s2 = "block";
                            upload_otherEndPointInputTag.type = "text";
                        } else {
                            s2 = "none";
                            s1 = "block";
                            upload_otherEndPointInputTag.type = "hidden";
                            upload_endPointTypeInputTag.type = "";
                        }
                        upload_endPointTypeInputTag.style.display = s1;
                        for (i= 0; i < upload_otherEndpointShowingTags.length; i++) {
                            upload_otherEndpointShowingTags[i].style.display = s2;
                        }
                        for (i= 0; i < upload_otherEndpointHidingTags.length; i++) {
                            upload_otherEndpointHidingTags[i].style.display = s1;
                        }
                    };
                    document.getElementsByName("endPoint")[0].onchange = function (e) {
                        upload_otherSelected = e.target.value === "other";
                        upload_loadLabels();
                    };
                    upload_loadLabels();
                    
                })();
                
                $(function() {
                    $(".log-menu").on('click',function() {
                        $('.log-panel').show();
                        $('form').hide();
                        $(this).addClass('current');
                        $('.conf-menu').removeClass('current');
                    })
                    
                    $('.conf-menu').on('click',function() {
                        $('form').show();
                        $('.log-panel').hide();
                        $(this).addClass('current');
                        $('.log-menu').removeClass('current');
                    })
                })
            }
        </script>
EOF;
        echo $script;
    }

    /**
     * 渲染日志
     * @return void
     */
    public static function logRender(): void
    {
        $log = new Log();
        $logFile = $log->getLogFile();
        $logColor = $log->getLogColor();
        $logContent = $log->getLogContent();
        $log = <<<EOF
    <div class="log-panel" style="display: none">
        <p>以下是本插件产生的错误日志，请定期查看并处理：</p>
        <p>日志文件是&nbsp;&nbsp;<span style="color:#666;font-size:8px">$logFile<span></p>
        <div style="width:98%;margin: 0 auto">
            <textarea style="color:$logColor;height:480px;width:100%;">$logContent</textarea>
        </div>
    </div> 
EOF;
        echo $log;
    }
}