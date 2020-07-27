<?php

namespace app\api\controller;

use app\api\model\statements\PointStatements;
use app\api\model\store\Shop as ShopModel;
use app\store\model\store\ShopSettled;


/**
 * 门店列表
 * Class Shop
 * @package app\api\controller
 */
class Shop extends Controller
{
    /**
     * 门店列表
     * @param string $longitude
     * @param string $latitude
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($longitude = '', $latitude = '',$shop_classify_id = 0,$search = '')
    {
        $model = new ShopModel;
        $list = $model->getList(true, $longitude, $latitude,$shop_classify_id,$search);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 获取距离用户最近的店铺
     * @param string $longitude
     * @param string $latitude
     * @param string $search
     */
    public function getShortOne($longitude = '', $latitude = '')
    {
        $model = new ShopModel;
        $list = $model->getShortOne($longitude, $latitude);
        return $this->renderSuccess(compact('list'));

    }

    /**
     * 门店详情
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail($shop_id)
    {
        $detail = ShopModel::detail($shop_id);
        return $this->renderSuccess(compact('detail'));
    }

    /**
     * 商家入驻
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function shopSettled()
    {
        //获取用户信息
        $user = $this->getUser();
        $model = new ShopSettled();
        if(!$this->request->isPost()){
            $info = $model->where(['user_id'=>$user['user_id'],'is_pass'=>0])->find();
            if($info){
                return $this->renderError('您已经申请，请耐心等待');
            }else{
                return $this->renderSuccess('您可以申请');
            }
        }
        $data = $this->postData();
        $model->save([
            'user_id' => $user['user_id'],
            'shop_name' => $data['shop_name'],
            'linkman' => $data['linkman'],
            'phone' => $data['phone']
        ]);
        return $this->renderError('您已经申请，请耐心等待');
    }

    /**
     * 获取商家积分信息
     */
    public function pointsInfo()
    {
        $user = $this->getUser();
        $shop_info = \app\api\model\store\Shop::get(['user_id'=>$user['user_id']]);
        $model = new PointStatements();
        return $this->renderSuccess([
            'list' => $model->getList($shop_info['shop_id']),
        ]);
    }

}