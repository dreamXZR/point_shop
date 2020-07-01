<?php

namespace app\api\controller;

use app\api\model\GoodsSku;
use app\store\model\PointGoods as GoodsModel;
use app\api\model\Cart as CartModel;
use app\common\service\qrcode\Goods as GoodsPoster;
use app\store\model\UserExchange;
use think\Db;

/**
 * 积分商品控制器
 * Class Goods
 * @package app\api\controller
 */
class PointGoods extends Controller
{
    /**
     * 积分商品列表
     * @param $category_id
     * @param $search
     * @param $sortType
     * @param $sortPrice
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($category_id, $search, $sortType, $sortPrice)
    {
        $model = new GoodsModel;
        $list = $model->getList(10, $category_id, $search, 'point',$sortType, $sortPrice);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($goods_id)
    {
        // 商品详情
        $detail = GoodsModel::detail($goods_id);
        if (!$detail || $detail['is_delete'] || $detail['goods_status']['value'] != 10) {
            return $this->renderError('很抱歉，商品信息不存在或已下架');
        }
        // 多规格商品sku信息
        $specData = $detail['spec_type'] == 20 ? $detail->getManySpecData($detail['spec_rel'], $detail['sku']) : null;

        return $this->renderSuccess(compact('detail', 'specData'));
    }

    /**
     *
     * 商品兑换
     * @param $goods_id
     * @param $goods_num
     * @param $goods_sku_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function exchange($goods_id,$goods_num,$goods_sku_id)
    {
        $user = $this->getUser();
        $goods = \app\api\model\Goods::detail($goods_id);
        // 判断商品是否下架
        if (!$goods || $goods['is_delete'] || $goods['goods_status']['value'] != 10) {
            return $this->renderError('很抱歉，商品信息不存在或已下架');
        }
        // 商品sku信息
        $goods['goods_sku'] = $goods->getGoodsSku($goods_sku_id);
        // 判断商品库存
        if ($goods_num > $goods['goods_sku']['stock_num']) {
            return $this->renderError('很抱歉，商品库存不足');
        }
        //判断积分是否充足
        if($user['points']<$goods_num * $goods['exchange_points']){
            return $this->renderError('很抱歉，您的剩余积分不足');
        }
        //商品兑换
        Db::startTrans();
        try {
            //记录兑换信息
            $exhangeModel = new UserExchange();
            $exhangeModel->save([
                'user_id' => $user['user_id'],
                'point_goods_id' => $goods_id,
                'point_goods_sku_id' => $goods_sku_id,
                'exchange_number' => $goods_num,
                'exchange_points' => $goods_num * $goods['exchange_points'],
                'exchange_code' => $this->make_coupon_card(),
                'goods_remarks' => $goods['goods_name'].' '.$goods['goods_sku']['goods_attr'],
            ]);
            //消减用户库存
            $user->decrPoints($goods_num * $goods['exchange_points']);
            //消减商品库存
            $this->updateGoodsStockNum([$goods],$goods_num);
            // 提交事务
            Db::commit();
            return $this->renderError('兑换成功');
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $this->renderError('很抱歉，兑换失败请重试');
        }

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
        $Qrcode = new GoodsPoster($detail, $this->getUser(false));
        return $this->renderSuccess([
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    private function make_coupon_card() {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return $d;
    }

    /**
     * 更新商品库存 (兑换商品)
     * @param $goods_list
     * @throws \Exception
     */
    private function updateGoodsStockNum($goods_list,$num)
    {
        $deductStockData = [];
        foreach ($goods_list as $goods) {
            // 兑换减库存
            $deductStockData[] = [
                'goods_sku_id' => $goods['goods_sku']['goods_sku_id'],
                'stock_num' => ['dec', $num]
            ];
        }
        !empty($deductStockData) && (new GoodsSku)->isUpdate()->saveAll($deductStockData);
    }

}
