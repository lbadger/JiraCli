<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Timer\FileTimer;


class ListTimersCommand extends Command {
    protected function configure() {
        $this->setName('timer:list')
            ->setDescription('List current timers');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $timer = new FileTimer();

        $data = $timer->getCurrentData();
        $table = new Table($output);
        $table->setHeaders(['name', 'start', 'stop']);

        $rows = [];

        foreach($data as $key => $times) {
            $rows[] = [
                $key,
                $times['start'],
                $times['stop']
            ];
        }

        $table->setRows($rows);
        $table->render();
    }
}