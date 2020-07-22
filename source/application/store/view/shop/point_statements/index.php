<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">商铺充值流水</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-9" style="float: right;">
                                <div class="am fr">
                                    <?php if ($admin_user['store_shop_id'] == null) : ?>
                                        <div class="am-form-group am-fl">
                                            <?php $shop_id = $request->get('shop_id') ?: null; ?>
                                            <select name="shop_id"
                                                    data-am-selected="{btnSize: 'sm', placeholder: '店铺名称'}">
                                                <option value=""></option>
                                                <?php if (isset($shop)): foreach ($shop as $first): ?>

                                                    <option value="<?= $first['shop_id'] ?>"
                                                        <?= $shop_id == $first['shop_id'] ? 'selected' : '' ?>>
                                                        <?= $first['shop_name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                        <div class="am-form-group am-fl">
                                            <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                                <div class="am-input-group-btn">
                                                    <button class="am-btn am-btn-default am-icon-search"
                                                            type="submit"></button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>店铺名称</th>
                                <th>充值时间</th>
                                <th>充值金额</th>
                                <th>充值积分</th>
<!--                                <th>备注</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['shop']['shop_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle"><?= $item['charge_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['points'] ?></td>
<!--                                    <td class="am-text-middle">--><?//= $item['remark'] ?><!--</td>-->
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
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
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('store.user/delete') ?>";
        $('.item-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

