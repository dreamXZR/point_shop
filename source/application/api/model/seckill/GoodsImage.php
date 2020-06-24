<?php

namespace app\api\model\seckill;

use app\common\model\seckill\GoodsImage as GoodsImageModel;

/**
 * 拼团商品图片模型
 * Class GoodsImage
 * @package app\api\model\sharing
 */
class GoodsImage extends GoodsImageModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
    ];

}
