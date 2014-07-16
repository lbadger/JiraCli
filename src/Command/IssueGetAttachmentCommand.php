<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\Util;

class IssueGetAttachmentCommand extends Command {
    protected function configure() {
        $this->setName('attach:get')
            ->setDescription('Get the attachment with the specified id')
            ->addArgument(
                'attachmentId',
                InputArgument::REQUIRED,
                'Attachment ID'
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to put the downloaded file (directory)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $attachmentId = $input->getArgument('attachmentId');
        $path = $input->getArgument('path');

        $jiraCli = Config::GetJiraCliFromConfig();

        $writePath = $jiraCli->GetAttachment($attachmentId, $path);

        $output->writeLn("Wrote attachment out to $writePath");
    }
}