<?php

namespace WCurtis\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\Timer\FileTimer;
use WCurtis\Timer\TimerAbstract;


class StartTimerCommand extends Command {
    protected function configure() {
        $this->setName('timer:start')
            ->setDescription('Start a timer')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Start the timer for the given issue',
                null
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Remove any timer already started for this issue',
                false
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $force = $input->getOption('force');

        $this->startTimer($issue, $force, $output, Config::GetNow(), new FileTimer());
    }

    protected function startTimer($issue, $force = false, OutputInterface $output, \DateTime $now, TimerAbstract $timer) {
        if($timer->GetStartTime($issue)) {
            if(!$force) throw new \Exception("Timer already started for issue $issue");

            $timer->ClearTimer($issue);
        }

        $timer->StartTimer($issue, $now);
        $output->writeLn("Set start time to " . $now->format('Y-m-d H:i:s'));
    }

} 