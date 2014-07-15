<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use WCurtis\Config;
use WCurtis\Util;

class LogListCommand extends Command {

    protected function configure() {
        $this->setName('log:list')
            ->setDescription('List JIRA worklogs')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue to list worklogs from'
            )
            ->addOption(
                'notjustme',
                NULL,
                InputOption::VALUE_NONE,
                'Show worklogs from users other than the currently configured user'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $onlyMe = !$input->getOption('notjustme');

        $jiraCli = Config::GetJiraCliFromConfig();

        Util::RenderTable($jiraCli->GetWorklogs($issue, $onlyMe), $output);
    }
}