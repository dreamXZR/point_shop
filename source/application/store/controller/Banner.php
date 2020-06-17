<?php

namespace app\store\controller;

use app\store\model\Category as CategoryModel;

/**
 * 商品分类
 * Class Category
 * @package app\store\controller\goods
 */
class Banner extends Controller
{
    //轮播图分类
    protected $banner_type_arr = [
        1 => '首页轮播图',
        2 => '店铺轮播图',
    ];

    /**
     * 轮播图列表
     * @return mixed
     */
    public function index()
    {
        $model = new \app\store\model\Banner();
        $list = $model->getlist();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除轮播图
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = \app\store\model\Banner::get($id);
        if (!$model->delete()) {
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
        $model = new \app\store\model\Banner();
        if (!$this->request->isAjax()) {
            // 获取分类
            $list = $this->banner_type_arr;
            return $this->fetch('add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('banner'))) {
            return $this->renderSuccess('添加成功', url('banner/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = \app\store\model\Banner::get($id, ['banner']);
        if (!$this->request->isAjax()) {
            // 获取所有分类
            $list = $this->banner_type_arr;
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('banner'))) {
            return $this->renderSuccess('更新成功', url('banner/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }



}
