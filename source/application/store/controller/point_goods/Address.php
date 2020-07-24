<?php

namespace app\store\controller\point_goods;

use app\store\controller\Controller;
use app\store\model\DeliveryAddress as DeliveryAddressModel;

/**
 * 积分商品配送地址
 * Class Delivery
 * @package app\store\controller\setting
 */
class Address extends Controller
{
    /**
     * 退货地址列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new DeliveryAddressModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加自提地址
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new DeliveryAddressModel;
        if ($model->add($this->postData('address'))) {
            return $this->renderSuccess('添加成功', url('point_goods.address/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑自提地址
     * @param $address_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($address_id)
    {
        // 模板详情
        $model = DeliveryAddressModel::detail($address_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('address'))) {
            return $this->renderSuccess('更新成功', url('point_goods.address/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除自提地址
     * @param $address_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($address_id)
    {
        $model = DeliveryAddressModel::detail($address_id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
