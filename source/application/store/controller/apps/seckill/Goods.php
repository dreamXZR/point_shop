<?php

namespace app\store\controller\apps\seckill;

use app\store\controller\Controller;
use app\store\model\Delivery;
use app\store\model\Delivery as DeliveryModel;
use app\store\model\Goods as GoodsModel;
use app\store\model\Category as CategoryModel;

/**
 * 秒杀商品管理控制器
 * Class Goods
 * @package app\store\controller\apps\sharing
 */
class Goods extends Controller
{

    /**
     * 秒杀商品列表
     * @param null $goods_status
     * @param null $category_id
     * @param string $goods_name
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($goods_status = null, $category_id = null, $goods_name = '')
    {
        // 商品分类
        //$catgory = CategoryModel::getCacheTree();
        // 商品列表
        $model = new GoodsModel;
        $list = $model->getList($goods_status, $category_id, $goods_name,'seckill');
        return $this->fetch('index', compact('list'));
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
            //$catgory = CategoryModel::getCacheTree();
            // 配送模板
            $delivery = (new Delivery)->getList();
            return $this->fetch('add', compact( 'delivery'));
        }
        $post_data = $this->postData('goods');
        $post_data['start_at'] = strtotime( $post_data['start_at']);
        $post_data['end_at'] = strtotime( $post_data['end_at']);
        if($post_data['end_at']<$post_data['start_at']){
            return $this->renderError('秒杀时间填写错误');
        }
        $post_data['is_seckill_goods'] = 1;
        $model = new GoodsModel;
        if ($model->add($post_data)) {
            return $this->renderSuccess('添加成功', url('apps.seckill.goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 复制主商城商品
     * @param $goods_id
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function copy_master($goods_id)
    {
        // 商品详情
        $model = \app\store\model\Goods::detail($goods_id);
        if (!$model || $model['is_delete']) {
            return $this->renderError('商品信息不存在');
        }
        if (!$this->request->isAjax()) {
            // 商品分类
            //$catgory = CategoryModel::getCacheTree();
            // 配送模板
            $delivery = (new Delivery)->getList();
            // 商品sku数据
            $specData = 'null';
            if ($model['spec_type'] == 20) {
                $specData = json_encode($model->getManySpecData($model['spec_rel'], $model['sku']), JSON_UNESCAPED_SLASHES);
            }
            return $this->fetch('copy_master', compact('model', 'delivery', 'specData'));
        }
        // 新增秒杀商品
        $post_data = $this->postData('goods');
        $post_data['start_at'] = strtotime( $post_data['start_at']);
        $post_data['end_at'] = strtotime( $post_data['end_at']);
        if($post_data['end_at']<$post_data['start_at']){
            return $this->renderError('秒杀时间填写错误');
        }
        $post_data['is_seckill_goods'] = 1;
        $model = new GoodsModel;
        if ($model->add($post_data)) {
            return $this->renderSuccess('添加成功', url('apps.seckill.goods/index'));
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
            $catgory = CategoryModel::getCacheTree();
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
            return $this->renderSuccess('添加成功', url('apps.sharing.goods/index'));
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
            //$catgory = CategoryModel::getCacheTree();
            // 配送模板
            $delivery = (new Delivery)->getList();
            // 商品sku数据
            $specData = 'null';
            if ($model['spec_type'] == 20) {
                $specData = json_encode($model->getManySpecData($model['spec_rel'], $model['sku']), JSON_UNESCAPED_SLASHES);
            }
            return $this->fetch('edit', compact('model', 'delivery', 'specData'));
        }
        $post_data = $this->postData('goods');
        $post_data['start_at'] = strtotime( $post_data['start_at']);
        $post_data['end_at'] = strtotime( $post_data['end_at']);
        if($post_data['end_at']<$post_data['start_at']){
            return $this->renderError('秒杀时间填写错误');
        }
        // 更新记录
        if ($model->edit($post_data)) {
            return $this->renderSuccess('更新成功', url('apps.seckill.goods/index'));
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
        //当前时间
        $current_time = time();
        if($model['goods_status']['value'] ==10  && $model['start_at']<$current_time){
            return $this->renderError('秒杀活动已开始，请勿删除');
        }
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
