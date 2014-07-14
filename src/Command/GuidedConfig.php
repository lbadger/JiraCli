<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/9/14
 * Time: 10:10 PM
 */

namespace WCurtis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;

class GuidedConfig {
    protected $command;
    protected $output;
    /** @var QuestionsSuck $dialog */
    protected $question;

    public function __construct(
        Command $command,
        OutputInterface $output,
        QuestionsSuck $question
    ) {
        $this->command = $command;
        $this->output = $output;
        $this->question = $question;
    }

    public function confirmStart() {
        return $this->question->confirm(
            "Sure you want to make a config? (y/n): ",
            false
        );
    }

    public function startConfig() {
        $question = $this->question;

        $url = $question->ask(
            'What\'s the URL to your JIRA instance? E.g., https://jira.example.com: '
        );

        $user = $question->ask(
            'User: '
        );

        $pass = $question->ask(
            'Password: ',
            false,
            true
        );

        $tz = date_default_timezone_get();

        $changeTz = $question->ask(
            "Enter your timezone, leave blank for PHP default ($tz): ",
            false
        );

        if($changeTz) $tz = $changeTz;

        $data = [
            'jira' => [
                'url' => $url,
                'user' => $user,
                'pass' => '**********'
            ],
            'tz' => $tz,
            'defaultCommentVisibility' => 'role.Developers'
        ];

        $response = $question->confirm(
            "\nAbout to write the following to ~/" . Config::$configFile . "\n\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n\nCool? (y/n): ",
            false
        );

        if(!$response) return;

        $data['jira']['pass'] = $pass;

        Config::Set($data);
    }

    public static function checkAndGuide(
        Command $command,
        OutputInterface $output,
        QuestionsSuck $question,
        $reconfigure = false
    ) {
        $guide = new GuidedConfig($command, $output, $question);

        if(Config::ConfigPresent()) {
            if(!$reconfigure) return;
            $output->writeLn("You already have a config");
        }

        if($guide->confirmStart()) {
            $guide->startConfig();
        }

        if(!Config::ConfigPresent()) {
            throw new \Exception("Oh well, either you said no or something horrible happened while writing the config.");
        }
    }
}
