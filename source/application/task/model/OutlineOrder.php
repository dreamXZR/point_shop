<?php

namespace app\task\model;

use app\api\model\store\Shop;
use app\common\service\Message;
use app\common\model\OutlineOrder as OrderModel;
use app\task\model\dealer\Apply as DealerApplyModel;
use app\task\model\statements\PointStatements;
use app\task\model\statements\UserPointStatements;
use app\task\model\WxappPrepayId as WxappPrepayIdModel;

/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class OutlineOrder extends OrderModel
{
    /**
     * 待支付订单详情
     * @param $order_no
     * @return null|static
     * @throws \think\exception\DbException
     */
    public function payDetail($order_no)
    {
        return self::get(['order_no' => $order_no, 'pay_status' => 10], ['user']);
    }

    /**
     * 订单支付成功业务处理
     * @param $transaction_id
     * @throws \Exception
     * @throws \think\Exception
     */
    public function paySuccess($transaction_id)
    {
        // 更新付款状态
        $this->updatePayStatus($transaction_id);
        // 发送消息通知
        $Message = new Message;
        $Message->payment($this);
        // 小票打印
//        $Printer = new Printer;
//        $Printer->printTicket($this, OrderStatusEnum::ORDER_PAYMENT);
    }

    /**
     * 更新付款状态
     * @param $transaction_id
     * @return false|int
     * @throws \Exception
     */
    private function updatePayStatus($transaction_id)
    {
        $this->startTrans();
        try {
            // 更新订单状态
            $this->save([
                'pay_status' => 20,
                'pay_time' => time(),
                'transaction_id' => $transaction_id
            ]);
            // 累积用户总消费金额
            $user = User::detail($this['user_id']);
            $user->cumulateMoney($this['pay_price']);
            //累计用户积分
            $user->incPoints($this['points']);
            //消减商家的积分
            $shop = Shop::get($this['shop_id']);
            $shop->decPoints($this['points']);
            //商家金额变更
            $shop->incMoney($this['total_price']);
            //记录积分日志
            $shop_log = new PointStatements();
            $shop_log->record([
                'user_id' => $this['user_id'],
                'shop_id' => $this['shop_id'],
                'type' => 10,
                'order_no' => $this['order_no'],
                'charge_money' => $this['total_price'],
                'points' => $this['points'],
                'remark' => '用户线下付款',
            ]);
            $user_log = new UserPointStatements();
            $user_log->save([
                'user_id' => $this['user_id'],
                'shop_id' => $this['shop_id'],
                'type' => 10,
                'points' => $this['points'],
                'remark' => '用户线下付款',
            ]);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            log_write($e->getMessage());
            $this->rollback();
            return false;
        }
    }

    /**
     * 购买指定商品成为分销商
     * @param $user_id
     * @param $goodsList
     * @param $wxapp_id
     * @return bool
     * @throws \think\exception\DbException
     */
    private function becomeDealerUser($user_id, $goodsList, $wxapp_id)
    {
        // 整理商品id集
        $goodsIds = [];
        foreach ($goodsList as $item) {
            $goodsIds[] = $item['goods_id'];
        }
        $model = new DealerApplyModel;
        return $model->becomeDealerUser($user_id, $goodsIds, $wxapp_id);
    }

    /**
     * 获取订单列表
     * @param array $filter
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($filter = [])
    {
        return $this->with(['goods' => ['refund']])->where($filter)->select();
    }

}
