<?php


namespace app\store\controller\shop;

use app\store\controller\Controller;
use  app\store\model\statements\PointStatements as PointStatementsModel;
use think\Session;

class PointStatements extends Controller
{
    public function index($shop_id = 0)
    {
        //商家列表
        $shop = (new \app\store\model\store\Shop())->field('shop_id,shop_name')->select();
        //后台人员信息
        $admin_user = Session::get('yoshop_store.user');
        $model = new PointStatementsModel();
        $list = $model->getShopRechargeList($shop_id);
        return $this->fetch('index',compact('list','shop','admin_user'));
    }
}