<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\Timer\FileTimer;
use WCurtis\Timer\TimerAbstract;


class StopTimerCommand extends Command {
    protected function configure() {
        $this->setName('timer:stop')
            ->setDescription('Stop a timer')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Stop the timer for the given issue',
                null
            )
            ->addOption(
                'send',
                's',
                InputOption::VALUE_NONE,
                'Send the worklog to JIRA'
            )
            ->addOption(
                'round',
                'r',
                InputOption::VALUE_REQUIRED,
                'Number of minutes to round to, when noround is not passed',
                15
            )
            ->addOption(
                'noround',
                'd',
                InputOption::VALUE_NONE,
                'Don\'t round the time'
            )
            ->addOption(
                'message',
                'm',
                InputOption::VALUE_REQUIRED,
                'Worklog message (requried when sending worklog)',
                null
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If timer already stopped, update with the new stop time (now)'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $timer = new FileTimer();
        $issue = $input->getArgument('issue');
        $force = $input->getOption('force');
        $now = Config::GetNow();

        if(!$timer->GetStartTime($issue)) {
            throw new \Exception("No timer started for $issue");
        }

        if($oldTime = $timer->GetStopTime($issue)) {
            if(!$force) throw new \Exception("Timer already stopped for $issue. Pass --force to ignore.");

            $timer->StopTimer($issue, $now);
        } else $timer->StopTimer($issue, $now);

        $elapsed = $timer->GetElapsed($issue, false);

        $output->writeLn("$elapsed minute(s) tracked.");

        $send = $input->getOption('send');

        if($send) {
            $round = $input->getOption('round');
            $message = $input->getOption('message');
            $noround = $input->getOption('noround');

            $args = [
                'command' => 'timer:log',
                'issue' => $issue,
                '--message' => $message,
                '--round' => $round,
            ];

            if($noround) $args['--noround'] = true;

            $command = $this->getApplication()->find('timer:log');
            $command->run(new ArrayInput($args), $output);
        }
    }
}
