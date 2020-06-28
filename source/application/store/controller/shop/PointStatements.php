<?php


namespace app\store\controller\shop;

use app\store\controller\Controller;
use  app\store\model\statements\PointStatements as PointStatementsModel;

class PointStatements extends Controller
{
    public function index()
    {
        $model = new PointStatementsModel();
        $list = $model->getShopList();
        return $this->fetch('index',compact('list'));
    }
}