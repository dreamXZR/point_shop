<?php

namespace app\store\controller;

use app\store\model\Category;
use app\store\model\Delivery;
use app\store\model\Goods as GoodsModel;
use think\Session;

/**
 * 商品管理控制器
 * Class Goods
 * @package app\store\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表(出售中)
     * @param null $goods_status
     * @param null $category_id
     * @param string $goods_name
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($goods_status = null, $category_id = null, $goods_name = '',$sortType = 'all',$sortPrice = false,$shop_id = 0)
    {
        // 商品分类
        //$catgory = Category::getCacheTree();
        //商家列表
        $shop = (new \app\store\model\store\Shop())->field('shop_id,shop_name')->select();
        //后台人员信息
        $admin_user = Session::get('yoshop_store.user');
        // 商品列表
        $model = new GoodsModel;
        $list = $model->getList($goods_status, $category_id, $goods_name,'goods',$sortType,$sortPrice,$shop_id);
        return $this->fetch('index', compact('list', 'shop','admin_user'));
    }

    /**
     * 添加商品
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function add()
    {

        if (!$this->request->isAjax()) {
            // 商品分类
            //$catgory = Category::getCacheTree();
            // 配送模板
            $delivery = (new Delivery)->getList();
            return $this->fetch('add', compact('catgory','delivery'));
        }
        $model = new GoodsModel;
        if ($model->add($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 一键复制
     * @param $goods_id
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function copy($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$this->request->isAjax()) {
            // 商品分类
            $catgory = Category::getCacheTree();
            // 配送模板
            $delivery = (new Delivery)->getList();
            // 商品sku数据
            $specData = 'null';
            if ($model['spec_type'] == 20) {
                $specData = json_encode($model->getManySpecData($model['spec_rel'], $model['sku']), JSON_UNESCAPED_SLASHES);
            }
            return $this->fetch('edit', compact('model', 'catgory', 'delivery', 'specData'));
        }
        $model = new GoodsModel;
        if ($model->add($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 商品编辑
     * @param $goods_id
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function edit($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$this->request->isAjax()) {
            // 商品分类
            //$catgory = Category::getCacheTree();
            // 配送模板
            $delivery = (new Delivery)->getList();
            // 商品sku数据
            $specData = 'null';
            if ($model['spec_type'] == 20) {
                $specData = json_encode($model->getManySpecData($model['spec_rel'], $model['sku']), JSON_UNESCAPED_SLASHES);
            }
            return $this->fetch('edit', compact('model', 'catgory', 'delivery', 'specData'));
        }
        // 更新记录
        if ($model->edit($this->postData('goods'))) {
            return $this->renderSuccess('更新成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 修改商品状态
     * @param $goods_id
     * @param boolean $state
     * @return array
     */
    public function state($goods_id, $state)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除商品
     * @param $goods_id
     * @return array
     */
    public function delete($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 禁用商品
     * @param $goods_id
     * @return array
     */
    public function takeoff($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->takeoff()) {
            return $this->renderError('禁用失败');
        }
        return $this->renderSuccess('禁用成功');
    }

    /**
     * 启用商品
     * @param $goods_id
     * @return array
     */
    public function takeon($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->takeon()) {
            return $this->renderError('启用失败');
        }
        return $this->renderSuccess('启用成功');
    }

}
