<?php
namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ConfigCommand extends Command {
    public function configure() {
        $this->setName('config')
            ->setDescription("Start a guided configuration wizard thing");
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $question = new QuestionsSuck($input, $output, $this);
        GuidedConfig::checkAndGuide($this, $output, $question, true);
    }

} 