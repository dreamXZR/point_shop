<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\ShopClassify as CategoryModel;

/**
 * 商品分类
 * Class Category
 * @package app\store\controller\goods
 */
class Classify extends Controller
{
    /**
     * 商品分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new CategoryModel;
        $list = $model->getCacheTree();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($category_id)
    {
        $model = CategoryModel::get($category_id);
        if (!$model->remove($category_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加商品分类
     * @return array|mixed
     */
    public function add()
    {
        $model = new CategoryModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('category'))) {
            return $this->renderSuccess('添加成功', url('shop.classify/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($category_id)
    {
        // 模板详情
        $model = CategoryModel::get($category_id, ['image']);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('category'))) {
            return $this->renderSuccess('更新成功', url('shop.classify/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
