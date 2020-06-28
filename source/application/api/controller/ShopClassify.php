<?php

namespace app\api\controller;

use app\api\model\store\ShopClassify as CategoryModel;

/**
 * 商品分类控制器
 * Class Goods
 * @package app\api\controller
 */
class ShopClassify extends Controller
{
    /**
     *
     * @return array
     * @throws \think\exception\DbException
     */
    public function index()
    {

        // 商品分类列表
        $list = array_values(CategoryModel::getCacheTree());
        return $this->renderSuccess(compact( 'list'));
    }

}
