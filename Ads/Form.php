<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/1
 * Time: 10:07 AM
 */

namespace Typecho\Plugin\Ads;

use Typecho\Common;
use Typecho\Db;
use Typecho\Db\Exception;
use Typecho\Request;
use Typecho\Widget;
use Typecho\Widget\Exception as WidgetException;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Widget\Helper\Form\Element\Hidden;
use Typecho\Widget\Helper\Form\Element\Submit;

class Ads_Form
{
    /**
     * 构建表单
     * @throws Exception
     * @throws WidgetException
     */
    public static function form(?string $action = null): Form
    {
        // 构建表单
        $options = Widget::widget('Widget_Options');
        $form = new Form(Common::url('/action/ads-edit', $options->index), Form::POST_METHOD);

        $adName = new Text('ad_name', null, null, _t('广告位名称*'));
        $form->addInput($adName);

        $adDesc = new Text('ad_desc', null, null, _t('广告位描述'));
        $form->addInput($adDesc);

        $adCode = new Textarea('ad_code', null, null, _t('广告位代码*'));
        $form->addInput($adCode);

        $sortIndex = new Text('sort_index', null, 1, _t('排序索引'), _t('必须为数字，默认数字越小排序越靠前'));
        $form->addInput($sortIndex);

        $do = new Hidden('do');
        $form->addInput($do);

        $id = new Hidden('id');
        $form->addInput($id);

        $submit = new Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        $request = Request::getInstance();

        if (($request->id ?? false) && 'insert' !== $action) {
            // 更新数据
            $db = Db::get();
            $prefix = $db->getPrefix();
            $ad = $db->fetchRow(
                $db->select()
                    ->from($prefix . 'ads')
                    ->where('id = ?', $request->id)
            );

            if (!$ad) {
                throw new WidgetException(_t('广告代码不存在'), 404);
            }

            $adName->value($ad['ad_name']);
            $adDesc->value($ad['ad_desc']);
            $adCode->value($ad['ad_code']);
            $sortIndex->value($ad['sort_index']);
            $do->value('update');
            $id->value($ad['id']);
            $submit->value(_t('编辑广告代码位'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('新增广告代码位'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        if ('insert' === $action || 'update' === 'action') {
            $adName->addRule('required', _t('必须填写广告位名称'));
            $adCode->addRule('required', _t('必须填写广告位代码'));
        }
        if ('update' == $action) {
            $id->addRule('required', _t('主键不存在'));
            $id->addRule([new Ads_Form, 'exists'], _t('广告代码位不存在'));
        }

        return $form;
    }


    /**
     * 渲染表单
     * @param Form $form
     * @return void
     */
    public static function render(Form $form): void
    {
        $form->render();
    }

    /**
     * 判断数据是否存在
     * @param int $id
     * @return bool
     */
    public static function exists(int $id): bool
    {
        try {
            $db = Db::get();

            $prefix = $db->getPrefix();
            $ad = $db->fetchRow(
                $db->select()
                    ->from($prefix . 'ads')
                    ->where('id = ?', $id)
                    ->limit(1)
            );
            return (bool)$ad;
        } catch (Exception) {
            return false;
        }
    }
}