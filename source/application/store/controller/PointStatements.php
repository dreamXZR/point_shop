<?php


namespace app\store\controller;

use  app\store\model\statements\PointStatements as PointStatementsModel;

class PointStatements extends Controller
{
    public function index()
    {
        $model = new PointStatementsModel();
        $list = $model->getList();
        return $this->fetch('index',compact('list'));
    }
}