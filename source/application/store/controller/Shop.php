<?php

namespace app\store\controller;

use app\store\controller\shop\Classify;
use app\store\model\ShopClassify;
use app\store\model\store\Shop as ShopModel;

/**
 * 门店管理
 * Class Shop
 * @package app\store\controller\store
 */
class Shop extends Controller
{
    /**
     * 门店列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ShopModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 腾讯地图坐标选取器
     * @return mixed
     */
    public function getpoint()
    {
        $this->view->engine->layout(false);
        return $this->fetch('getpoint');
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new ShopModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('shop'))) {
            return $this->renderSuccess('添加成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $shop_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($shop_id)
    {
        // 门店详情
        $model = ShopModel::detail($shop_id);
        if (!$this->request->isAjax()) {
            // 店铺分类
            $catgory = ShopClassify::getCacheTree();
            return $this->fetch('edit', compact('model','catgory'));
        }
        // 新增记录
        if ($model->edit($this->postData('shop'),false)) {
            return $this->renderSuccess('更新成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($shop_id)
    {
        // 门店详情
        $model = ShopModel::detail($shop_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    public function recharge($shop_id)
    {

        // 门店详情
        $model = ShopModel::detail($shop_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('recharge', compact('model'));
        }
        //积分充值
        $data = $this->postData('shop');
        if($data['points'] == 0){
            return $this->renderError('请输入充值积分');
        }
        if($model->recharge($data)){
            return $this->renderSuccess('充值成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '充值失败');
    }



}