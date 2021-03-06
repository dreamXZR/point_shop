<?php


namespace app\store\model\statements;

use app\common\model\statements\PointStatements as PointStatementsModel;
use think\Session;

class PointStatements extends PointStatementsModel
{
    private $admin_user;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->admin_user = Session::get('yoshop_store.user');
    }

    public function getList()
    {
        $where['type'] = ['in',[10,20,30]];
        if($shop_id = $this->admin_user['store_shop_id']){
            $where['shop_id'] = $shop_id;
        }
        return $this->where($where)
            ->with('shop')
            ->order(['id' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    public function getRemarkAttr($value,$data)
    {
        if($data['type'] == 10){
            if($data['charge_money'] == 0){
                return $value;
            }
            return $value.'-- ￥'.$data['charge_money'];
        }
        if($data['type'] == 20){
            return $value."-- 订单号:".$data['order_no'];
        }
        if($data['type'] == 30){
            return $value.'-- ￥'.$data['charge_money'];
        }
        return $value;
    }
}