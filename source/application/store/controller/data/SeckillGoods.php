<?php

namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\Goods as GoodsModel;

/**
 * 商品数据控制器
 * Class Goods
 * @package app\store\controller\data
 */
class SeckillGoods extends Controller
{
    /* @var \app\store\model\Goods $model */
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new GoodsModel;
        $this->view->engine->layout(false);
    }

    /**
     * 商品列表
     * @param null $status
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function lists($status = null)
    {
        $shop_id = (new \app\store\model\store\Shop())
            ->where(['is_delete'=>0,'status'=>1,'admin_status'=>1])
            ->column('shop_id');
        $list = $this->model->getList($status,0,'','seckill','all',false,$shop_id);
        return $this->fetch('list', compact('list'));
    }

}
