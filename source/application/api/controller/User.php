<?php

namespace app\api\controller;

use app\api\model\OutlineOrder;
use app\api\model\User as UserModel;
use app\api\model\Wxapp as WxappModel;
use app\api\model\WxappPrepayId as WxappPrepayIdModel;
use app\common\library\wechat\WxPay;
use app\store\model\UserExchange;
use think\Db;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{
    /**
     * 用户自动登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => $model->login($this->request->post()),
            'token' => $model->getToken()
        ]);
    }

    /**
     * 用户兑换记录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exchangeLists($is_used)
    {
        $user = $this->getUser();
        $fields = [
            'ue.id as id',
            'g.goods_name',
            'ue.exchange_number',
            'ue.exchange_points',
            'ue.is_used',
        ];
        $where = [
            'is_used' => $is_used,
            'user_id' => $user['user_id']
        ];
        $joins = [
            ['goods g','ue.point_goods_id = g.goods_id','left']
        ];
        $exchangeRecord = Db::name('user_exchange')->alias('ue')->field($fields)->where($where)->join($joins)->select();
        return $this->renderSuccess([
            'list'=>$exchangeRecord
        ]);
    }

    public function exchangeDetail($id)
    {
        $user = $this->getUser();
        $data = UserExchange::get($id);
        return $this->renderSuccess([
            'info'=>$data
        ]);
    }

    /**
     * 用户线下支付
     */
    public function outlinePay()
    {
        $user = $this->getUser();
        $data = $this->request->post();
        $outlineOrderModel = new OutlineOrder();
        //创建线下付款订单
        if($outlineOrderModel->createOrder($user['user_id'],$data)){
            // 发起微信支付
            return $this->renderSuccess([
                'payment' => $this->unifiedorder($outlineOrderModel),
                'order_id' => $outlineOrderModel['order_id']
            ]);
        }
    }

    /**
     * 构建微信支付
     * @param $order
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    private function unifiedorder($order)
    {
        $user = $this->getUser();
        // 统一下单API
        $wxConfig = WxappModel::getWxappCache();
        $WxPay = new WxPay($wxConfig);
        $payment = $WxPay->unifiedorder($order['order_no'], $user['open_id'], $order['pay_price'],'outline');
        // 记录prepay_id
        $model = new WxappPrepayIdModel;
        $model->add($payment['prepay_id'], $order['order_id'], $user['user_id'], 10);
        return $payment;
    }

}
