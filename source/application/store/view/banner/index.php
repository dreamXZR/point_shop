<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">轮播图管理</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('banner/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('banner/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>图片</th>
                                <th>分类</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $first['banner']['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $first['banner']['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle"><?= $first['image_type'] == 1 ? '首页轮播图':'店铺轮播图' ?></td>
                                    <td class="am-text-middle"><?= $first['sort'] ?></td>
                                    <td class="am-text-middle"><?= $first['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('banner/edit')): ?>
                                                <a href="<?= url('banner/edit',
                                                    ['id' => $first['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('banner/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('banner/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

