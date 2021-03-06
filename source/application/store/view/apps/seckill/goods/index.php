<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">限时特惠商品列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('apps.seckill.goods/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('apps.seckill.goods/add') ?>">
                                                <span class="am-icon-plus"></span> 新增商品
                                            </a>
                                        </div>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="j-copyAdd am-btn am-btn-default am-btn-secondary"
                                               href="javascript:void(0);">
                                                <span class="am-icon-plus"></span> 复制主商城商品
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $goods_status = $request->get('goods_status') ?: null; ?>
                                        <select name="goods_status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '商品状态'}">
                                            <option value=""></option>
                                            <option value="10"
                                                <?= $goods_status == 10 ? 'selected' : '' ?>>上架
                                            </option>
                                            <option value="20"
                                                <?= $goods_status == 20 ? 'selected' : '' ?>>下架
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="goods_name"
                                                   placeholder="请输入商品名称"
                                                   value="<?= $request->get('goods_name') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>商品ID</th>
                                <?php if (ifSupertube()): ?>
                                    <th>店铺ID</th>
                                <?php endif; ?>
                                <th>商品图片</th>
                                <th>商品名称</th>
                                <th>活动开始时间</th>
                                <th>活动结束时间</th>
                                <th>积分</th>
                                <th>商品排序</th>
                                <th>商品状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['goods_id'] ?></td>
                                    <?php if (ifSupertube()): ?>
                                        <td class="am-text-middle"><?= $item['shop_id'] ?></td>
                                    <?php endif; ?>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['image'][0]['file_path'] ?>"
                                           title="点击查看大图" target="_blank">
                                            <img src="<?= $item['image'][0]['file_path'] ?>"
                                                 width="50" height="50" alt="商品图片">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['goods_name'] ?></p>
                                    </td>
                                    <td class="am-text-middle"><?= date('Y-m-d H:i',$item['start_at']) ?></td>
                                    <td class="am-text-middle"><?= date('Y-m-d H:i',$item['end_at']) ?></td>
                                    <td class="am-text-middle"><?= $item['exchange_points'] ?></td>
                                    <td class="am-text-middle"><?= $item['goods_sort'] ?></td>
                                    <td class="am-text-middle">
                                           <span class="j-state am-badge x-cur-p
                                           am-badge-<?= $item['admin_goods_status'] == 10 && $item['goods_status']['value'] == 10 ? 'success' : 'warning' ?>"
                                                 data-id="<?= $item['goods_id'] ?>"
                                                 data-state="<?= $item['goods_status']['value'] ?>">
                                               <?= $item['admin_goods_status'] == 10 ? $item['goods_status']['text']:'后台禁用' ?>
                                           </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.seckill.goods/edit')): ?>
                                                <a href="<?= url('apps.seckill.goods/edit',
                                                    ['goods_id' => $item['goods_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('apps.seckill.goods/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['goods_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <?php if (ifSupertube()): ?>
                                                <?php if ($item['admin_goods_status'] == 10): ?>
                                                    <a href="javascript:;" class="item-takeoff tpl-table-black-operation-del"
                                                       data-id="<?= $item['goods_id'] ?>">
                                                        后台禁用
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($item['admin_goods_status'] == 20): ?>
                                                    <a href="javascript:;" class="item-takeon tpl-table-black-operation-green"
                                                       data-id="<?= $item['goods_id'] ?>">
                                                        后台启用
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="tpl-copyForm" type="text/template">
    <div class="am-padding-top-sm">
        <form class="j-copyForm am-form tpl-form-line-form">
            <div class="am-form-group">
                <label class="am-u-sm-3 am-form-label"> 商品ID </label>
                <div class="am-u-sm-8 am-u-end">
                    <input type="number" class="j-goods_id tpl-form-input" name="goods_id" required>
                    <small>可在 <a href="<?= url('goods/index') ?>" target="_blank">商品管理 - 商品列表</a> 中查看
                    </small>
                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        /**
         * 复制主商城商品
         */
        $('.j-copyAdd').click(function () {
            var $copyForm = $('#tpl-copyForm');
            var URL = "<?= url('apps.seckill.goods/copy_master')?>";
            layer.open({
                type: 1
                , title: '复制主商城商品'
                , area: '340px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: $copyForm.html()
                , success: function (layero) {

                }
                , yes: function (index, layero) {
                    var goodsId = layero.find('.j-goods_id').val();
                    if (goodsId > 0) {
                        window.location = URL + '&goods_id=' + goodsId;
                    }
                    layer.close(index);
                }
            });
        });

        // 商品状态
        $('.j-state').click(function () {
            // 验证权限
            if (!"<?= checkPrivilege('apps.sharing.goods/state')?>") {
                return false;
            }
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data.state) === 10 ? '下架' : '上架') + '该商品吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('apps.sharing.goods/state') ?>"
                        , {
                            goods_id: data.id,
                            state: Number(!(parseInt(data.state) === 10))
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });

        // 删除元素
        var url = "<?= url('apps.seckill.goods/delete') ?>";
        $('.item-delete').delete('goods_id', url);

        //禁用商品
        var url = "<?= url('apps.seckill.goods/takeoff') ?>";
        $('.item-takeoff').operate('goods_id', url,'是否禁用该商品？');

        //启用商品
        var url = "<?= url('apps.seckill.goods/takeon') ?>";
        $('.item-takeon').operate('goods_id', url,'是否启用该商品？');

    });
</script>

