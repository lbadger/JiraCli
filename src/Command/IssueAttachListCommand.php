<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\Util;

class IssueAttachListCommand extends Command {
    protected function configure() {
        $this->setName('attach:list')
            ->setDescription('List attachments on the specified issue')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue key'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');

        $jiraCli = Config::GetJiraCliFromConfig();

        $result = $jiraCli->ListAttachments($issue);

        Util::RenderTable($result, $output);
    }
}