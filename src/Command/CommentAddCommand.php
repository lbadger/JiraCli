<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;

class CommentAddCommand extends Command {
    protected function configure() {
        $this->setName('comment:add')
            ->setDescription('Add a comment to the provided issue.')
            ->addArgument(
                'issue',
                InputArgument::REQUIRED,
                'Issue to clear the timer for'
            )
            ->addArgument(
                'comment',
                InputArgument::REQUIRED,
                'Body of the comment'
            )
            ->addOption(
                'visibility',
                'b',
                InputOption::VALUE_REQUIRED,
                'Visibility to set on the issue',
                'DEFAULT'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $issue = $input->getArgument('issue');
        $body = $input->getArgument('comment');
        $visibility = $input->getOption('visibility');

        if($visibility === 'DEFAULT') {
            $visibility = Config::get('defaultCommentVisibility');

            if(!$visibility) {
                throw new \Exception('No visibility provided. Set the "defaultCommentVisibility" key in your config (~/.jiraCliConfig) to, e.g., "role.Developers"');
            }
        }

        $jiraCli = Config::GetJiraCliFromConfig();

        $jiraCli->AddComment($issue, $body, $visibility);
    }
}
