<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\Order as OrderModel;
use app\common\service\Order as OrderService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\api\model\store\shop\Clerk as ClerkModel;

/**
 * 自提订单管理
 * Class Order
 * @package app\api\controller\shop
 */
class Order extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser();   // 用户信息
    }


    /**
     * 商家店铺订单列表
     * @param $dataType
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($dataType,$shop_id)
    {
        $model = new OrderModel;
        $list = $model->getShopOrderList($shop_id, $dataType);
        return $this->renderSuccess(compact('list'));
    }


    /**
     * 订单发货
     */
    public function delivery($order_id)
    {
         OrderModel::update(['delivery_status'=>20],['order_id'=>$order_id]);
         return $this->renderSuccess('操作成功');
    }

    /**
     * 核销订单详情
     * @param $order_id
     * @param int $order_type
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($order_id, $order_type = OrderTypeEnum::MASTER)
    {
        // 订单详情
        $order = OrderService::getOrderDetail($order_id, $order_type);
        // 验证是否为该门店的核销员
        $clerkModel = ClerkModel::detail(['user_id' => $this->user['user_id']]);
        return $this->renderSuccess(compact('order', 'clerkModel'));
    }

    /**
     * 确认核销
     * @param $order_id
     * @param int $order_type
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function extract($order_id, $order_type = OrderTypeEnum::MASTER)
    {
        // 订单详情
        $order = OrderService::getOrderDetail($order_id, $order_type);
        // 验证是否为该门店的核销员
        $ClerkModel = ClerkModel::detail(['user_id' => $this->user['user_id'],'shop_id'=>$order['shop_id']]);
        if (!$ClerkModel->checkUser($order['extract_shop_id'])) {
            return $this->renderError($ClerkModel->getError());
        }
        // 确认核销
        if ($order->extract($ClerkModel['clerk_id'])) {
            return $this->renderSuccess([], '订单核销成功');
        }
        return $this->renderError($order->getError() ?: '核销失败');
    }

}