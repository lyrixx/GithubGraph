<?php

namespace Lyrixx\GithubGraph\Model;

class IssuesCollection
{
    public $issues;
    public $issuesOpen;

    public $prs;
    public $prsOpen;

    private $nbIssuesOpenedByDay;
    private $nbPrsOpenedByDay;
    private $nbIssuesClosedByDay;
    private $nbPrsClosedByDay;

    public function __construct()
    {
        $this->issues = new \SplObjectStorage();
        $this->issuesOpen = new \SplObjectStorage();
        $this->prs = new \SplObjectStorage();
        $this->prsOpen = new \SplObjectStorage();

        $this->resetNbByDay();
    }

    public function incrementNbIssuesOpenedByDay()
    {
        $this->nbIssuesOpenedByDay++;
    }

    public function incrementNbPrsOpenedByDay()
    {
        $this->nbPrsOpenedByDay++;
    }

    public function incrementNbIssuesClosedByDay()
    {
        $this->nbIssuesClosedByDay++;
    }

    public function incrementNbPrsClosedByDay()
    {
        $this->nbPrsClosedByDay++;
    }

    public function resetNbByDay()
    {
        $this->nbIssuesOpenedByDay = 0;
        $this->nbPrsOpenedByDay = 0;
        $this->nbIssuesClosedByDay = 0;
        $this->nbPrsClosedByDay = 0;
    }

    public function count()
    {
        return array(
            'issues' => count($this->issues),
            'issuesOpen' => count($this->issuesOpen),
            'prs' => count($this->prs),
            'prsOpen' => count($this->prsOpen),
            'nbIssuesOpenedByDay' => $this->nbIssuesOpenedByDay,
            'nbPrsOpenedByDay' => $this->nbPrsOpenedByDay,
            'nbIssuesClosedByDay' => $this->nbIssuesClosedByDay,
            'nbPrsClosedByDay' => $this->nbPrsClosedByDay,
        );
    }
}
