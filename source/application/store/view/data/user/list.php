<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <link rel="stylesheet" href="assets/common/css/amazeui.min.css"/>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?= $version ?>"/>
    <script src="assets/common/js/jquery.min.js"></script>
    <title>拼团商品列表</title>
</head>
<body class="select-data">
<div class="am-scrollable-horizontal am-u-sm-12">
    <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
        <thead>
        <tr>
            <th>
                <label class="am-checkbox">
                    <input data-am-ucheck data-check="all" type="checkbox">
                </label>
            </th>
            <th>微信头像</th>
            <th>微信昵称</th>
            <th>累积消费金额</th>
            <th>性别</th>
            <th>国家</th>
            <th>省份</th>
            <th>城市</th>
            <th>注册时间</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
            <tr>
                <td class="am-text-middle">
                    <label class="am-checkbox">
                        <input data-am-ucheck data-check="item" data-params='<?= json_encode([
                            'user_id' => (string)$item['user_id'],
                            'nickName' => $item['nickName'],
                            'avatarUrl' => $item['avatarUrl'],
                        ], JSON_UNESCAPED_SLASHES) ?>' type="checkbox">
                    </label>
                </td>
                <td class="am-text-middle">
                    <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                        <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                    </a>
                </td>
                <td class="am-text-middle"><?= $item['nickName'] ?></td>
                <td class="am-text-middle"><?= $item['money'] ?></td>
                <td class="am-text-middle"><?= $item['gender'] ?></td>
                <td class="am-text-middle"><?= $item['country'] ?: '--' ?></td>
                <td class="am-text-middle"><?= $item['province'] ?: '--' ?></td>
                <td class="am-text-middle"><?= $item['city'] ?: '--' ?></td>
                <td class="am-text-middle"><?= $item['create_time'] ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="8" class="am-text-center">暂无记录</td>
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

<script src="assets/common/js/amazeui.min.js"></script>
<script>

    /**
     * 获取已选择的数据
     * @returns {Array}
     */
    function getSelectedData() {
        var data = [];
        $('input[data-check=item]:checked').each(function () {
            data.push($(this).data('params'));
        });
        return data;
    }

    $(function () {

        // 全选框元素
        var $checkAll = $('input[data-check=all]')
            , $checkItem = $('input[data-check=item]')
            , itemCount = $checkItem.length;

        // 复选框: 全选和反选
        $checkAll.change(function () {
            $checkItem.prop('checked', this.checked);
        });

        // 复选框: 子元素
        $checkItem.change(function () {
            if (!this.checked) {
                $checkAll.prop('checked', false);
            } else {
                var checkedItemNum = $checkItem.filter(':checked').length;
                checkedItemNum === itemCount && $checkAll.prop('checked', true);
            }
        });

    });
</script>
</body>
</html>
