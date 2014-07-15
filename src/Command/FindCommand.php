<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/9/14
 * Time: 11:04 PM
 */

namespace WCurtis\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WCurtis\Config;
use WCurtis\JiraCli;
use WCurtis\Util;

class FindCommand extends Command{
    /** @var OutputInterface */
    protected $output;

    /** @var  InputInterface */
    protected $input;

    protected function configure() {
        $this->setName('issue:find')
            ->setDescription('Find issues by filter or JQL')
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Run Filter pointed to by provided ID'
            )
            ->addOption(
                'jql',
                'j',
                InputOption::VALUE_OPTIONAL,
                'Direct JQL query'
            )
            ->addOption(
                'listFilters',
                'l',
                InputOption::VALUE_NONE,
                'List your favorite filters'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->output = $output;
        $this->input = $input;

        $filterId = $input->getOption('filter');
        $jql = $input->getOption('jql');

        if($filterId) {
            $this->runFilter($filterId);
            return;
        }

        if($jql) {
            $this->runJql($jql);
            return;
        }

        if($input->getOption('listFilters')) {
            $this->listFilters();
            return;
        }
    }

    protected function runJql($jql) {
        $jiraCli = Config::GetJiraCliFromConfig();

        $issues = $jiraCli->RunJql($jql);

        Util::RenderTable($issues, $this->output);
    }

    protected function runFilter($filterId) {
        $jiraCli = Config::GetJiraCliFromConfig();

        $issues = $jiraCli->RunFilter($filterId);

        Util::RenderTable($issues, $this->output);
    }

    protected function listFilters() {
        $jiraCli = Config::GetJiraCliFromConfig();

        $filters = $jiraCli->GetFilters();

        Util::RenderTable($filters, $this->output);
    }
}
