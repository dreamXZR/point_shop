<?php

namespace app\store\controller;

use app\store\model\store\Shop as ShopModel;
use think\Db;
use think\Session;

/**
 * 门店管理
 * Class Shop
 * @package app\store\controller\store
 */
class StoreShop extends Controller
{

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
     * 编辑门店
     * @param $shop_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        //获取登录信息
        $admin_user = Session::get('yoshop_store')['user'];
        $user_info = \app\store\model\store\User::get($admin_user['store_user_id']);
        // 门店详情
        if(!$user_info['store_shop_id']){
            return $this->renderError( '用户暂时无法使用');
        }
        $model = ShopModel::detail($user_info['store_shop_id']);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('shop'))) {
            return $this->renderSuccess('更新成功', url('store_shop/edit'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}