<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use WCurtis\Config;
use WCurtis\JiraCli;

class CommentListCommand extends Command {
    protected function configure() {
        $this->setName('comment:list')
            ->setDescription('List comments for the provided issue.')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue to clear the timer for'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');

        $jiraCli = Config::GetJiraCliFromConfig();

        $comments = $jiraCli->ListComments($issue);

        $table = new Table($output);
        $table->setHeaders(array_keys(JiraCli::$commentMap));

        $rows = array_map(function($f) {
            return array_values($f);
        }, $comments);

        $table->setRows($rows);
        $table->render();
    }
}
