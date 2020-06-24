<?php

namespace app\api\controller\seckill;

use app\api\controller\Controller;
use app\api\model\seckill\Goods as GoodsModel;
use app\common\service\qrcode\Goods as GoodsPoster;
use app\api\model\sharing\Active as ActiveModel;

/**
 * 商品控制器
 * Class Goods
 * @package app\api\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表
     * @param int $category_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($store_shop_id = 0)
    {
        $model = new GoodsModel;
        $list = $model->getList(10, $store_shop_id);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return array
     */
    public function detail($goods_id)
    {
        // 商品详情
        $detail = GoodsModel::detail($goods_id);
        if (!$detail || $detail['is_delete'] || $detail['goods_status']['value'] != 10) {
            return $this->renderError('很抱歉，商品信息不存在或已下架');
        }
        if($detail['seckill_status'] == 30){
            return $this->renderError('很抱歉，秒杀活动已结束');
        }
        // 多规格商品sku信息
        $specData = $detail['spec_type'] == 20 ? $detail->getManySpecData($detail['spec_rel'], $detail['sku']) : null;
        return $this->renderSuccess(compact('detail', 'specData'));
    }

    /**
     * 获取推广二维码
     * @param $goods_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function poster($goods_id)
    {
        // 商品详情
        $detail = GoodsModel::detail($goods_id);
        // 生成推广二维码
        $Qrcode = new GoodsPoster($detail, $this->getUser(false), 20);
        return $this->renderSuccess([
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

}
