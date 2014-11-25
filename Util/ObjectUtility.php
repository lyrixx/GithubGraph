<?php

namespace Lyrixx\GithubGraph\Util;

use Lyrixx\GithubGraph\Console\Report\ReportBuilder;
use Lyrixx\GithubGraph\Graphite\Client;
use Lyrixx\GithubGraph\Model\Object;
use Lyrixx\GithubGraph\Model\ObjectsCollection;

class ObjectUtility
{
    public function __construct(Client $graphite)
    {
        $this->graphite = $graphite;
    }

    public function sort($objects)
    {
        usort($objects, function (Object $v1, Object $v2) {
            return $v1->getOpenAt() > $v2->getOpenAt();
        });

        return $objects;
    }

    public function hydrate(array $objects, $type, ReportBuilder $reportBuilder = null)
    {
        $reportBuilder and $reportBuilder->startProgress(count($objects), 20);

        $githubObjects = array();
        foreach ($objects as $object) {
            $githubObject = new Object();
            $githubObject->mapFromGithubApi($object, $type);
            $githubObjects[] = $githubObject;

            $reportBuilder and $reportBuilder->advanceProgress();
        }

        $reportBuilder and $reportBuilder->endProgress();

        return $githubObjects;
    }

    public function createHistory(array $objects)
    {
        $firstDay = clone $objects[0]->getOpenAt();
        $firstDay->modify('-1 day');
        $objectsCollection = new ObjectsCollection();
        for ($day = $firstDay; $day < new \DateTime('tomorrow'); $day->modify('+1day')) {
            $objectsCollection->resetNbByDay();
            foreach ($objects as $k => $object) {
                if ($object->isOpenedOn($day)) {
                    if ($object->isPullRequest()) {
                        $objectsCollection->prs->attach($object);
                        $objectsCollection->incrementNbPrsOpenedByDay();
                    } else {
                        $objectsCollection->issues->attach($object);
                        $objectsCollection->incrementNbIssuesOpenedByDay();
                    }
                }

                if ($object->isClosedOn($day)) {
                    if ($object->isPullRequest()) {
                        $objectsCollection->incrementNbPrsClosedByDay();
                    } else {
                        $objectsCollection->incrementNbIssuesClosedByDay();
                    }
                }

                if ($object->isOpenOn($day)) {
                    if ($object->isPullRequest()) {
                        $objectsCollection->prsOpen->attach($object);
                    } else {
                        $objectsCollection->issuesOpen->attach($object);
                    }
                } else {
                    if ($object->isPullRequest()) {
                        $objectsCollection->prsOpen->detach($object);
                    } else {
                        $objectsCollection->issuesOpen->detach($object);
                    }
                    // Optimization: If the issue is closed, no need to re-compute it
                    if ($objectsCollection->issues->contains($object) || $objectsCollection->prs->contains($object)) {
                        unset($objects[$k]);
                    }
                }
                // Optimization: If the current issue is not yet open, so all
                // other issue will be in the same state
                if ($day < $object->getOpenAt()) {
                    break;
                }
            }

            yield $day => $objectsCollection;
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

        foreach ($history as $day => $objectsCollection) {
            $counts = $objectsCollection->count();
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
