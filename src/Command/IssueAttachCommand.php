<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\Util;

class IssueAttachCommand extends Command {
    protected function configure() {
        $this->setName('attach:put')
            ->setDescription('Attach the specified file to the specified issue')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue key'
            )
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Attachment Filename'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $filename = $input->getArgument('filename');

        $jiraCli = Config::GetJiraCliFromConfig();

        $result = $jiraCli->Attach($filename, $issue);

        echo print_r($result, true);
    }
}