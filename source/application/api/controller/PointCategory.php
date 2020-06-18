<?php

namespace app\api\controller;

use app\store\model\PointCategory as PointCategoryModel;
use app\api\model\WxappCategory as WxappCategoryModel;

/**
 * 积分商品分类控制器
 * Class Goods
 * @package app\api\controller
 */
class PointCategory extends Controller
{
    /**
     * 分类页面
     * @return array
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 分类模板
        $templet = WxappCategoryModel::detail();
        // 商品分类列表
        $list = array_values(PointCategoryModel::getCacheTree());
        return $this->renderSuccess(compact('templet', 'list'));
    }

}
