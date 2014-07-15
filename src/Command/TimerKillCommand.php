<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Timer\FileTimer;

class TimerKillCommand extends Command {
    protected function configure() {
        $this->setName('timer:kill')
            ->setDescription('Kill provided timer')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue to clear the timer for'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $timer = new FileTimer();

        $start = $timer->GetStartTime($issue);

        if(!$start) {
            $output->writeLn("No timer for issue $issue");
            return;
        }

        $timer->ClearTimer($issue);
    }
}