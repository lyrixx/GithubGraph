<?php

namespace Lyrixx\GithubGraph\Console\Command;

use Lyrixx\GithubGraph\Console\Report\ReportBuilder;
use Lyrixx\GithubGraph\Github\Github;
use Lyrixx\GithubGraph\Util\IssueUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeRepositoryCommand extends Command
{
    private $github;
    private $issueUtility;

    public function __construct(Github $github, IssueUtility $issueUtility)
    {
        $this->github = $github;
        $this->issueUtility = $issueUtility;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('analyze')
            ->setDescription('Fetch issues and inject them in metric')
            ->addArgument('repository', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($organisation, $repositoryName) = $this->extractRepositoryName($input->getArgument('repository'));

        $reportBuilder = new ReportBuilder($output);

        $output->writeln('<info>Download issues</info>');
        $issuesOpened = $this->github->getIssues($organisation, $repositoryName, 'open', $reportBuilder);
        $issuesClosed = $this->github->getIssues($organisation, $repositoryName, 'close', $reportBuilder);

        $issues = array_merge($issuesOpened, $issuesClosed);

        $issues = $this->issueUtility->sort($issues);

        $output->writeln('<info>Hydrate issues</info>');
        $issues = $this->issueUtility->hydrate($issues, $input->getArgument('repository'), $reportBuilder);
        $output->writeln(sprintf('<info>Oldest issue was created on <comment>%s</comment></info>', $issues[0]->getOpenAt()->format('Y-m-d')));

        $history = $this->issueUtility->createHistory($issues);

        $output->writeln('<info>Replay History</info>');
        $history = $this->issueUtility->replayHistory($history, $organisation, $repositoryName, $issues[0]->getOpenAtDay(), $reportBuilder);

        $output->writeln('<info>Finished</info>');
    }

    protected function extractRepositoryName($string)
    {
        if (!preg_match('#^(?<organisation>[a-zA-Z0-9]+)/(?P<repositoryName>[a-zA-Z0-9\-_\.]+)$#', $string, $matches)) {
            throw new \InvalidArgumentException(sprintf('Can not extract organisation and repositoryName from: "%s". Should seems like "symfony/symfony"', $string));
        }

        return array($matches['organisation'], $matches['repositoryName']);
    }
}
