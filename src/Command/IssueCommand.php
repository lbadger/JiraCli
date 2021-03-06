<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\JiraCli;
use WCurtis\Util;

class IssueCommand extends Command {
    protected function configure() {
        $this->setName('issue:show')
            ->setDescription('Show the specified issue')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue key'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $jiraCli = Config::GetJiraCliFromConfig();

        $result = $jiraCli->RunJql("key = '$issue'");

        Util::RenderTable($result, $output);
    }
}