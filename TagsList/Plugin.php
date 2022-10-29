<?php

use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Helper\Form;

/**
 * 后台编辑文章时增加标签选择列表
 *
 * @package TagsList
 * @author Pony
 * @version 1.2.0
 * @link https://mayanpeng.cn
 */
class TagsList_Plugin implements PluginInterface
{
    public static function activate()
    {
        $method = "tagsList";
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('TagsList_Plugin', $method);
    }

    /* 禁用插件方法 */
    public static function deactivate()
    {
    }

    public static function config(Form $form)
    {

    }

    public static function personalConfig(Form $form)
    {
    }


    /**
     * 标签列表
     * @return void
     */
    public static function tagsList(): void
    {
        Widget::widget('Widget_Metas_Tag_Cloud')->to($tags);
        if (!$tags->have()) {
            return;
        }

        $lists = "";
        $id = 0;
        while ($tags->next()) {
            $name = (string)$tags->name;
            $lists .= <<<EOF
            <a id="$id" data-name="$name">$name</a>
EOF;
            $id++;
        }

        $html = <<<EOF
        <style>
            .tags-list a {
                cursor: pointer;
                padding: 0 6px;
                margin: 2px 0;
                display: inline-block;
                border-radius: 2px;
                text-decoration: none;
            }.tags-list a:hover {
                background: #ccc;
                color: #fff;
            }
        </style>;
        <script>
            $(document).ready(function () {
                let elements = '<div style="margin-top: 35px;" class="tags-list">';
                elements += '<ul style="list-style: none;border: 1px solid #D9D9D6;padding: 6px 12px; max-height: 240px;overflow: auto;background-color: #FFF;border-radius: 2px;">'
                elements += '$lists';
                elements += '</ul></div>';
                $('#tags').after(elements);
                
                $('.tags-list').find("a").click(function() {
                    let name =$(this).attr("data-name")
                    $("#tags").tokenInput('add',{id:name,tags:name})
                })
            });
        </script>
EOF;
        echo $html;
    }
}
