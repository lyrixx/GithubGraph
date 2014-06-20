<?php

namespace Lyrixx\GithubGraph\Util;

use Lyrixx\GithubGraph\Console\Report\ReportBuilder;
use Lyrixx\GithubGraph\Graphite\Api as GraphiteApi;
use Lyrixx\GithubGraph\Model\Issue;
use Lyrixx\GithubGraph\Model\IssuesCollection;

class IssueUtility
{
    public function __construct(GraphiteApi $graphite)
    {
        $this->graphite = $graphite;
    }

    public function sort($issues)
    {
        usort($issues, function ($v1, $v2) {
            return $v1['number'] > $v2['number'];
        });

        return $issues;
    }

    public function hydrate(array $issues, $repository, ReportBuilder $reportBuilder = null)
    {
        $reportBuilder and $reportBuilder->startProgress(count($issues), 20);

        $githubIssues = array();
        foreach ($issues as $issue) {
            $githubIssue = new Issue();
            $githubIssue->mapFromGithubApi($issue, $repository);
            $githubIssues[] = $githubIssue;

            $reportBuilder and $reportBuilder->advanceProgress();
        }

        $reportBuilder and $reportBuilder->endProgress();

        return $githubIssues;
    }

    public function createHistory(array $issues)
    {
        $firstDay = clone $issues[0]->getOpenAtDay();
        $githubIssues = new IssuesCollection();
        for ($day = $firstDay; $day < new \DateTime('tomorrow'); $day->modify('+1day')) {
            $githubIssues->resetNbByDay();
            foreach ($issues as $k => $issue) {
                if ($issue->isOpenedAtDay($day)) {
                    if ($issue->isPullRequest()) {
                        $githubIssues->incrementNbPrsOpenedByDay();
                    } else {
                        $githubIssues->incrementNbIssuesOpenedByDay();
                    }
                }

                if ($issue->isClosedAtDay($day)) {
                    if ($issue->isPullRequest()) {
                        $githubIssues->incrementNbPrsClosedByDay();
                    } else {
                        $githubIssues->incrementNbIssuesClosedByDay();
                    }
                }

                if ($issue->isOpenAtDay($day)) {
                    if ($issue->isPullRequest()) {
                        $githubIssues->prs->attach($issue);
                        $githubIssues->prsOpen->attach($issue);
                    } else {
                        $githubIssues->issues->attach($issue);
                        $githubIssues->issuesOpen->attach($issue);

                    }
                } else {
                    if ($issue->isPullRequest()) {
                        $githubIssues->prsOpen->detach($issue);
                    } else {
                        $githubIssues->issuesOpen->detach($issue);
                    }
                    // Optimization: If the issue is closed, no need to re-compute it
                    if ($githubIssues->issues->contains($issue) || $githubIssues->prs->contains($issue)) {
                        unset($issues[$k]);
                    }
                }
                // Optimization: If the current issue is not yet open, so all
                // other issue will be in the same state
                if ($day < $issue->getOpenAtDay()) {
                    break;
                }
            }
            yield $day => $githubIssues;
        }
    }

    public function replayHistory($history, $organisation, $repository, $firstDay, ReportBuilder $reportBuilder = null)
    {
        $repository = strtr($repository, array(
            '-' => '_',
            '.' => '_',
        ));
        $prefix = "$organisation.$repository.";

        $reportBuilder and $reportBuilder->startProgress((new \DateTime('tomorrow'))->diff($firstDay)->days);

        foreach ($history as $day => $issuesCollection) {
            $counts = $issuesCollection->count();
            $timestamp = $day->format('U');

            $this->graphite->push($prefix.'issue.total', $counts['issues'], $timestamp);
            $this->graphite->push($prefix.'issue.open', $counts['issuesOpen'], $timestamp);
            $this->graphite->push($prefix.'issue.opened', $counts['nbIssuesOpenedByDay'], $timestamp);
            $this->graphite->push($prefix.'issue.closed', $counts['nbIssuesClosedByDay'], $timestamp);

            $this->graphite->push($prefix.'pr.total', $counts['prs'], $timestamp);
            $this->graphite->push($prefix.'pr.open', $counts['prsOpen'], $timestamp);
            $this->graphite->push($prefix.'pr.opened', $counts['nbPrsOpenedByDay'], $timestamp);
            $this->graphite->push($prefix.'pr.closed', $counts['nbPrsClosedByDay'], $timestamp);

            $this->graphite->flush();

            $reportBuilder and $reportBuilder->advanceProgress();
        }

        $reportBuilder and $reportBuilder->endProgress();
    }
}
