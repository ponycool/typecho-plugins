<?php
/**
 * 广告投放管理
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/10/31
 * Time: 2:43 PM
 */

require_once('Form.php');

use Typecho\Db;
use Typecho\Plugin\Ads\Ads_Form;

include 'common.php';
include 'header.php';
include 'menu.php';
?>

    <div class="main">
        <div class="body container">
            <?php include 'page-title.php'; ?>
            <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs clearfix">
                        <li class="current">
                            <a href="<?php $options->adminUrl('extending.php?panel=Ads%2FManageAds.php') ?>">
                                <?php _e('广告投放'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://mayanpeng.cn/archives/205.html" title="查看广告投放使用帮助"
                               target="_blank">
                                <?php _e('帮助'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-mb-12 col-tb-8" role="main">
                    <?php
                    $prefix = $db->getPrefix();
                    $ads = $db->fetchAll(
                        $db->select()
                            ->from($prefix . 'ads')
                            ->where($prefix . 'ads.deleted = ?', 0)
                            ->order($prefix . 'ads.sort_index', Db::SORT_ASC)
                            ->order($prefix . 'ads.id', Db::SORT_ASC)
                    );
                    ?>
                    <form method="post" name="manage_categories" class="operate-form">
                        <div class="typecho-list-operate clearfix">
                            <div class="operate">
                                <label>
                                    <i class="sr-only"><?php _e('全选'); ?></i>
                                    <input type="checkbox" class="typecho-table-select-all">
                                </label>
                                <div class="btn-group btn-drop">
                                    <button class="btn dropdown-toggle btn-s" type="button">
                                        <i class="sr-only"><?php _e('操作'); ?></i>
                                        <?PHP _e('选中项'); ?>
                                        <i class="i-caret-down"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="<?php $options->index('/action/ads-edit?do=delete'); ?>"
                                               lang="<?php _e('你确认要删除这些广告代码位吗？'); ?>">
                                                <?php _e('删除'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="typecho-table-wrap">
                            <table class="typecho-list-table">
                                <colgroup>
                                    <col width="20">
                                    <col width="20">
                                    <col width="40%">
                                    <col width="20%">
                                    <col width="20%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th></th>
                                    <th><?php _e('ID'); ?></th>
                                    <th><?php _e('广告位名称'); ?></th>
                                    <th><?php _e('展现次数'); ?></th>
                                    <th><?php _e('排序索引'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($ads)): $alt = 0; ?>
                                    <?php foreach ($ads as $item): ?>
                                        <tr id="id-<?php echo $item['id']; ?>">
                                            <td>
                                                <input type="checkbox" value="<?php echo $item['id'] ?>" name="id[]">
                                            </td>
                                            <td><?php echo $item['id'] ?></td>
                                            <td>
                                                <a href="<?php echo $request->makeUriByRequest('id=' . $item['id']); ?>"
                                                   title="点击编辑">
                                                    <?php echo $item['ad_name'] ?>
                                                </a>
                                            </td>
                                            <td><?php echo $item['view_times'] ?></td>
                                            <td><?php echo $item['sort_index'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">
                                            <h6 class="typecho-list-table-title">
                                                <?php _e('没有任何广告投放代码'); ?>
                                            </h6>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="col-mb-12 col-tb-4" role="form">
                    <?php Ads_Form::render(Ads_Form::form()); ?>
                </div>
            </div>
        </div>
    </div>

<?php

include 'copyright.php';
include 'common-js.php';
?>

    <script type="text/javascript">
        (function () {
            $(function () {
                const table = $('.typecho-list-table').tableDnD({
                    onDrop: function () {
                        let ids = []

                        $('input[type=checkbox]', table).each(function () {
                            ids.push($(this).val());
                        })

                        $.post(
                            '<?php $options->index('action/ads-edit?do=sort');?>',
                            $.param({id: ids})
                        );

                        $('tr', table).each(function (i) {
                            if (i % 2) {
                                $(this).addClass('even');
                            } else {
                                $(this).removeClass('even');
                            }
                        });
                    }
                });

                table.tableSelectable({
                    checkEl: 'input[type=checkbox]',
                    rowEl: 'tr',
                    selectAllEl: '.typecho-table-select-all',
                    actionEl: '.dropdown-menu a'
                });

                $('.btn-drop').dropdownMenu({
                    btnEl: '.dropdown-toggle',
                    menuEl: '.dropdown-menu'
                });

                $('.dropdown-menu button.merge').click(function () {
                    const btn = $(this)
                    btn.parents('form').attr('action', btn.attr('rel')).submit();
                });

                <?php if(($request->id ?? false)):?>
                $('.typecho-mini-panel').effect('highlight', '#AACB36');
                <?php endif;?>
            })
        })()
    </script>

<?php include 'footer.php'; ?>