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
                InputOption::VALUE_OPTIONAL,
                'Round to the nearest 15, or number provided, minutes',
                false
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

        $output->writeLn("Set stop time to " . $timer->GetStopTime($issue)->format('Y-m-d H:i:s'));

        $send = $input->getOption('send');

        if($send) {
            $round = $input->getOption('round');

            $round = (int)($round === false ? 0 : $round);


            $message = $input->getOption('message');

            if(!$message) throw new \Exception("Message is required when sending a worklog");

            $jiraCli = Config::GetJiraCliFromConfig();

            $elapsed = $timer->GetElapsed($issue, !!$round, $round);

            $jiraCli->AddWorklog($issue, $timer->GetStartTime($issue), $message, $elapsed);

            $timer->ClearTimer($issue);

            $output->writeLn("\nDone, $elapsed minutes logged to $issue");
        }
    }
}
