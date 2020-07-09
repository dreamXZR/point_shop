<?php


namespace app\api\command;


use app\common\model\Order;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Test extends Command
{
    protected function configure()
    {
        $this->setName('orderFinish')->setDescription('订单状态更新、积分分发');
    }

    protected function execute(Input $input, Output $output)
    {
        $order = new \app\task\behavior\Order();
        $order->run(new Order());
        $output->writeln("finish");
    }
}