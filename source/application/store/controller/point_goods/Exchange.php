<?php


namespace app\store\controller\point_goods;

use app\store\controller\Controller;
use app\store\model\UserExchange;


class Exchange extends Controller
{
    public function index()
    {
        $model = new UserExchange();

        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    public function exchange()
    {
        $exchange_id = request()->post('id');
        $model = UserExchange::get($exchange_id);
        $model->save([
            'is_used' => 1
        ]);
        return $this->renderSuccess('兑换成功');
    }
}