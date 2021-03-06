<?php

namespace app\task\behavior;

use think\Cache;
use app\task\model\Setting;
use app\task\model\Order as OrderModel;
use app\task\model\dealer\Order as DealerOrderModel;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 订单行为管理
 * Class Order
 * @package app\task\behavior
 */
class Order
{
    /* @var \app\task\model\Order $model */
    private $model;

    public $error = '';

    /**
     * 执行函数
     * @param $model
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function run($model)
    {
        if (!$model instanceof OrderModel) {
            return new OrderModel and false;
        }
        $this->model = $model;
        $this->model->startTrans();
        try {
            $config = Setting::getItem('trade',config('mini_weixin.wxapp_id'));
            // 未支付订单自动关闭
            $this->close($config['order']['close_days']);
            // 已发货订单自动确认收货
            $this->receive($config['order']['receive_days']);
            // 订单的积分计算
            $this->calculatePoints($config['order']['refund_days']);
            $this->model->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->model->rollback();
            return false;
        }
//        if (!$model::$wxapp_id) {
//            return false;
//        }

        //if (!Cache::has('__task_space__order__' .config('mini_weixin.wxapp_id'))) {

            //Cache::set('__task_space__order__' . config('mini_weixin.wxapp_id'), time(), 3600);
        //}
        return true;
    }

    /**
     * 未支付订单自动关闭
     * @param $close_days
     * @return $this|bool
     */
    private function close($close_days)
    {
        // 取消n天以前的的未付款订单
        if ($close_days < 1) {
            return false;
        }
        // 截止时间
        $deadlineTime = time() - ((int)$close_days * 86400);
        // 条件
        $filter = [
            'pay_status' => 10,
            'order_status' => 10,
            'create_time' => ['<', $deadlineTime]
        ];
        // 查询截止时间未支付的订单
        $orderIds = $this->model->where($filter)->column('order_id');
        // 记录日志
        $this->dologs('close', [
            'close_days' => (int)$close_days,
            'deadline_time' => $deadlineTime,
            'orderIds' => json_encode($orderIds),
        ]);
        // 直接更新
        if (!empty($orderIds)) {
            return $this->model->isUpdate(true)->save(['order_status' => 20], ['order_id' => ['in', $orderIds]]);
        }
        return false;
    }

    /**
     * 已发货订单自动确认收货
     * @param $receive_days
     * @return bool|false|int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function receive($receive_days)
    {
        if ($receive_days < 1) {
            return false;
        }
        // 截止时间
        $deadlineTime = time() - ((int)$receive_days * 86400);
        // 条件
        $filter = [
            'pay_status' => 20,
            'delivery_status' => 20,
            'receipt_status' => 10,
            'delivery_time' => ['<', $deadlineTime]
        ];
        // 订单id集
        $orderIds = $this->model->where($filter)->column('order_id');
        // 记录日志
        $this->dologs('receive', [
            'receive_days' => (int)$receive_days,
            'deadline_time' => $deadlineTime,
            'orderIds' => json_encode($orderIds),
        ]);
        // 更新订单收货状态
        $this->model->isUpdate(true)->save([
            'receipt_status' => 20,
            'receipt_time' => time(),
            'order_status' => 30
        ], ['order_id' => ['in', $orderIds]]);
        // 发放分销订单佣金
        //return $this->grantMoney($orderIds);
        return true;
    }

    private function calculatePoints($refund_days)
    {
        if ($refund_days < 1) {
            return false;
        }
        //截止时间
        $deadlineTime = time() - ((int)$refund_days * 86400);
        $filter = [
            'pay_status' => 20,
            'delivery_status' => 20,
            'receipt_status' => 20,
            'receipt_time' => ['<',$deadlineTime],
            'is_points' => 10
        ];

        // 订单id集
        $orderIds = $this->model->where($filter)->column('order_id');
        // 记录日志
        $this->dologs('calculatePoints', [
            'refund_days' => (int)$refund_days,
            'deadline_time' => $deadlineTime,
            'orderIds' => json_encode($orderIds),
        ]);
        // 更新订单积分状态
        $this->model->isUpdate(true)->save([
            'is_points' => 20,
        ], ['order_id' => ['in', $orderIds]]);
        //积分分发
        return $this->distributePoints($orderIds);
    }

    /**
     * 发放分销订单佣金
     * @param $orderIds
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function grantMoney($orderIds)
    {
        $list = $this->model->getList(['order_id' => ['in', $orderIds]]);
        if ($list->isEmpty()) {
            return false;
        }
        foreach ($list as &$order) {
            DealerOrderModel::grantMoney($order, OrderTypeEnum::MASTER);
        }
        return true;
    }

    private function distributePoints($orderIds)
    {
        $list = $this->model->getList(['order_id' => ['in', $orderIds]]);
        if ($list->isEmpty()) {
            return false;
        }
        foreach ($list as &$order){
            $order->distributePoints();
        }
        return true;
    }

    /**
     * 记录日志
     * @param $method
     * @param array $params
     * @return bool|int
     */
    private function dologs($method, $params = [])
    {
        $value = 'behavior Order --' . $method;
        foreach ($params as $key => $val)
            $value .= ' --' . $key . ' ' . $val;
        return log_write($value);
    }

}
