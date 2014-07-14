<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;

class LogCommand extends Command {

    protected function configure() {
        $this->setName('log:add')
            ->setDescription('Add a worklog to JIRA')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue to log time under'
            )
            ->addArgument(
                'time',
                InputArgument::REQUIRED,
                'JIRA time logged string (e.g., \'1h 30m\''
            )
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Worklog message'
            )
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_REQUIRED,
                'Date to log work on',
                'now'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $time = $input->getArgument('time');
        $message = $input->getArgument('message');
        $date = Config::GetDate($input->getOption('date'));

        $jiraCli = Config::GetJiraCliFromConfig();

        $jiraCli->AddWorklog($issue, $date, $message, $time, true);

        $output->writeLn("\nDone, $time logged to $issue");
    }
}
