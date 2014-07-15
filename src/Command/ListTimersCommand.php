<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Timer\FileTimer;
use WCurtis\Util;

class ListTimersCommand extends Command {
    protected function configure() {
        $this->setName('timer:list')
            ->setDescription('List current timers');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $timer = new FileTimer();

        $data = $timer->getCurrentData();

        $rows = Util::DictMap($data, function($k, $v) {
            return [
                'name' => $k,
                'start' => $v['start'],
                'stop' => $v['stop']
            ];
        });

        Util::RenderTable($rows, $output);

    }
}