<?php


namespace app\api\model;


use think\Model;
use app\common\service\Order as OrderService;

class OutlineOrder extends Model
{
    protected $name = 'outline_order';

    public $error;

    public function createOrder($user_id,$data)
    {
        $this->startTrans();
        try {
            $this->save([
                'user_id' => $user_id,
                'order_no' => $this->orderNo(),
                'total_price' => $data['total_price'],
                'pay_price' => $data['total_price'],
                'shop_id' => $data['shop_id'],
                'points' => $this->getPoints($data['total_price'])
            ]);
            $this->commit();
        }catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 生成订单号
     * @return string
     */
    protected function orderNo()
    {
        return OrderService::createOrderNo();
    }

    protected function getPoints($price)
    {
        //金额兑换积分
        return 100;
    }
}