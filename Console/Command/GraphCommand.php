<?php

namespace Lyrixx\GithubGraph\Console\Command;

use Lyrixx\GithubGraph\Console\Report\ReportBuilder;
use Lyrixx\GithubGraph\Github\Github;
use Lyrixx\GithubGraph\Util\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GraphCommand extends Command
{
    private $github;
    private $objectUtility;

    public function __construct(Github $github, ObjectUtility $objectUtility)
    {
        $this->github = $github;
        $this->objectUtility = $objectUtility;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('graph')
            ->setDescription('Fetch issues and inject them in graphite')
            ->addArgument('repository', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($organisation, $repositoryName) = $this->extractRepositoryName($input->getArgument('repository'));

        $reportBuilder = new ReportBuilder($output);

        $output->writeln('<info>Download issues</info>');
        $issues = $this->github->getIssues($organisation, $repositoryName, $reportBuilder);

        $output->writeln('<info>Download pull requests</info>');
        $pullRequests = $this->github->getPullRequests($organisation, $repositoryName, $reportBuilder);

        $output->writeln('<info>Hydrate issues</info>');
        $issues = $this->objectUtility->hydrate($issues, 'issue', $reportBuilder);

        $output->writeln('<info>Hydrate pull requests</info>');
        $pullRequests = $this->objectUtility->hydrate($pullRequests, 'pull_request', $reportBuilder);

        $output->writeln('<info>Merge and sort issues and pull requests</info>');
        $objects = array_merge($issues, $pullRequests);
        $objects = $this->objectUtility->sort($objects);

        if (!$objects) {
            $output->writeln('<info>The repository does not contain any issues or pull requests</info>');

            return 0;
        }

        $output->writeln(sprintf('<info>Oldest issue was created on <comment>%s</comment></info>', $objects[0]->getOpenAt()->format('Y-m-d')));

        $history = $this->objectUtility->createHistory($objects);

        $output->writeln('<info>Replay History</info>');
        $history = $this->objectUtility->replayHistory($history, $organisation, $repositoryName, $objects[0]->getOpenAt(), $reportBuilder);

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
