<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\store\ShopSettled as ShopSettledModel;

class Settled extends Controller
{
    /**
     * 商家店铺入驻申请
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ShopSettledModel();
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除当前入驻申请
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        $model = new ShopSettledModel();
        $model->where(['id'=>$id])->delete();
        return $this->renderSuccess('删除成功');
    }

    /**
     * 审核通过操作
     * @param $id
     * @return array|bool
     */
    public function settled_pass($id)
    {
        $model = new ShopSettledModel();
        $result = $model->settled_pass($id);
        if($result){
            return $this->renderSuccess('审核通过');
        }else{
            return $this->renderError('操作失败，请重新操作');
        }

    }
}