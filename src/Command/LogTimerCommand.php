<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\Timer\FileTimer;

class LogTimerCommand extends Command {
    protected function configure() {
        $this->setName('timer:log')
            ->setDescription('Log the specified timer to JIRA')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Log the timer for the given issue',
                null
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
                'Worklog message',
                null
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If timer is not yet stopped, stop it (although you probably should have just used timer:stop)'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');

        $message = $input->getOption('message');
        $shouldRound = !$input->getOption('noround');
        $round = $input->getOption('round');
        $force = $input->getOption('force');

        $timer = new FileTimer();


        $round = (int)$round;

        if(!$message) throw new \Exception("Message is required when sending a worklog");

        if(!$timer->GetStopTime($issue)) {
            if(!$force) {
                throw new \Exception("Timer is not yet stopped. Pass --force, or use timer:stop");
            }

            $timer->StopTimer($issue);
        }

        $jiraCli = Config::GetJiraCliFromConfig();

        $elapsed = $timer->GetElapsed($issue, $shouldRound, $round);

        $jiraCli->AddWorklog($issue, $timer->GetStartTime($issue), $message, $elapsed);

        $timer->ClearTimer($issue);

        $output->writeLn("\nDone, $elapsed minutes logged to $issue");
    }



} 