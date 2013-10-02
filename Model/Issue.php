<?php

namespace Lyrixx\GithubGraph\Model;

class Issue
{
    private $repository;
    private $issueId;
    private $status;
    private $isPullRequest;
    private $openAt;
    private $closeAt;

    private $openAtDay;
    private $closeAtDay;

    public function mapFromGithubApi(array $issue, $repository)
    {
        $this->repository = $repository;
        $this->issueId = $issue['number'];
        $this->isPullRequest = (boolean) $issue['pull_request']['html_url'];
        $this->status = $issue['state'];
        $this->openAt = new \Datetime($issue['created_at']);
        if ($issue['closed_at']) {
            $this->closeAt = new \Datetime($issue['closed_at']);
        }
    }

    public function isOpenAtDay(\Datetime $day)
    {
        // We want to compare only on day
        if (!$this->openAtDay) {
            $this->openAtDay = new \Datetime($this->openAt->format('Y-m-d'));
        }

        if ($day < $this->openAtDay) {
            return false;
        }

        if (null === $this->closeAt) {
            return true;
        }

        if (!$this->closeAtDay) {
            $this->closeAtDay = new \Datetime($this->closeAt->format('Y-m-d'));
        }

        if ($day <= $this->closeAtDay) {
            return true;
        }

        return false;
    }

    public function isOpenedAtDay(\Datetime $day)
    {
        // We want to compare only on day
        if (!$this->openAtDay) {
            $this->openAtDay = new \Datetime($this->openAt->format('Y-m-d'));
        }

        $day = new \Datetime($day->format('Y-m-d'));;

        return $day == $this->openAtDay;
    }

    public function isClosedAtDay(\Datetime $day)
    {
        if (!$this->closeAt) {
            return false;
        }

        // We want to compare only on day
        if (!$this->closeAtDay) {
            $this->closeAtDay = new \Datetime($this->closeAt->format('Y-m-d'));
        }

        $day = new \Datetime($day->format('Y-m-d'));;

        return $day == $this->closeAtDay;
    }

    public function getOpenAt()
    {
        return $this->openAt;
    }

    public function getOpenAtDay()
    {
        if (!$this->openAtDay) {
            $this->openAtDay = new \Datetime($this->openAt->format('Y-m-d'));
        }

        return $this->openAtDay;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function isPullRequest()
    {
        return $this->isPullRequest;
    }

    public function __toString()
    {
        return (string) $this->issueId;
    }
}
