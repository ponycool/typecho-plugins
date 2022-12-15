<?php
/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/12/14
 * Time: 4:48 PM
 */

use Typecho\Db;

include 'common.php';
include 'header.php';
include 'menu.php';
?>
    <style type="text/css">
        .imgPreview {
            display: none;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            position: fixed;
            background: rgba(0, 0, 0, 0.5);
        }

        .imgPreview img {
            z-index: 100;
            width: 60%;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .img {
            cursor: auto;
        }
    </style>
    <div class="main">
        <div class="body container">
            <?php include 'page-title.php'; ?>
            <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs clearfix">
                        <li class="current">
                            <a href="<?php $options->adminUrl('extending.php?panel=Upload%2FMedia.php') ?>">
                                <?php _e('媒体管理'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://mayanpeng.cn/archives/219.html" title="查看广告投放使用帮助"
                               target="_blank">
                                <?php _e('帮助'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-mb-12 col-tb-12" role="main">
                    <?php
                    $prefix = $db->getPrefix();
                    $media = $db->fetchAll(
                        $db->select()
                            ->from($prefix . 'media')
                            ->where($prefix . 'media.deleted = ?', 0)
                            ->order($prefix . 'media.id', Db::SORT_DESC)
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
                                            <a href="<?php $options->index('/action/media-edit?do=delete'); ?>"
                                               lang="<?php _e('你确认要删除这些媒体文件吗？'); ?>">
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
                                    <col width="30%">
                                    <col width="80">
                                    <col width="80">
                                    <col width="80">
                                    <col width="80">
                                    <col width="20%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th></th>
                                    <th><?php _e('ID'); ?></th>
                                    <th><?php _e('文件名'); ?></th>
                                    <th><?php _e('媒体类型'); ?></th>
                                    <th><?php _e('大小'); ?></th>
                                    <th><?php _e('扩展名'); ?></th>
                                    <th><?php _e('对象存储'); ?></th>
                                    <th><?php _e('服务器保留'); ?></th>
                                    <th><?php _e('图片'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($media)): $alt = 0; ?>
                                    <?php foreach ($media as $item): ?>
                                        <tr id="id-<?php echo $item['id']; ?>">
                                            <td>
                                                <input type="checkbox" value="<?php echo $item['id'] ?>" name="id[]">
                                            </td>
                                            <td><?php echo $item['id'] ?></td>
                                            <td>
                                                <?php echo $item['file_name'] ?>
                                            </td>
                                            <td><?php echo $item['type'] ?></td>
                                            <td><?php echo $item['size_by_unit'] . "MB" ?></td>
                                            <td><?php echo $item['ext'] ?></td>
                                            <td><?php echo $item['object_storage'] ?></td>
                                            <td><?php echo $item['server_replica'] ? '是' : '否' ?></td>
                                            <td><?php
                                                if ($item['external_url']) {
                                                    echo '<a href="#" title="点击放大"><img class="avatar thumbnail" src="' . $item['external_url'] . '" alt="' . $item['file_name'] . '" width="32" height="32"/></a>';
                                                } else {
                                                    $options = Typecho_Widget::widget('Widget_Options');
                                                    $nopic_url = Typecho_Common::url('/usr/plugins/Upload/Static/nopic.jpg', $options->siteUrl);
                                                    echo '<img class="avatar" src="' . $nopic_url . '" alt="NOPIC" width="32" height="32"/>';
                                                }
                                                ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12">
                                            <h6 class="typecho-list-table-title">
                                                <?php _e('没有任何媒体'); ?>
                                            </h6>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="imgPreview">
        <img src="#" alt="" id="imgPreview">
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
                            '<?php $options->index('action/media-edit?do=sort');?>',
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

                // 图片预览
                $('.thumbnail').on('click', function () {
                    let src = $(this).attr("src")
                    $('.imgPreview img').attr('src', src)
                    $('.imgPreview').show()
                })
                $('.imgPreview').on('click', function () {
                    $('.imgPreview').hide()
                })
            })
        })()
    </script>

<?php include 'footer.php'; ?>