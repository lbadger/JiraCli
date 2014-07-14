<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/9/14
 * Time: 11:09 PM
 */

namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class QuestionsSuck {
    protected $input;
    protected $output;
    protected $command;
    /** @var QuestionHelper */
    protected $question;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Command $command
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->command = $command;

        $this->question = $command->getHelperSet()->get('question');
    }

    protected function getAndAsk($message, $type, $default, $hidden) {
        $question = $this->getQuestion($message, $type, $default);

        if($hidden) $question->setHidden(true);

        return $this->question->ask(
            $this->input,
            $this->output,
            $question
        );
    }

    public function confirm($message, $default) {
        return $this->getAndAsk($message, 'confirm', $default, false);
    }

    public function ask($message, $default = null, $hidden = false) {
        return $this->getAndAsk($message, 'value', $default, $hidden);
    }

    protected function getQuestion($message, $type, $default) {
        switch($type) {
            case 'confirm':
                return new ConfirmationQuestion($message, $default);
            case 'value':
                return new Question($message, $default);
            default:
                throw new \Exception("Invalid question type $type");
        }
    }
}
