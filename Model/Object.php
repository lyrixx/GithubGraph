<?php

namespace Lyrixx\GithubGraph\Model;

class Object
{
    private $type;
    private $number;
    private $status;
    private $openAt;
    private $closeAt;

    public function mapFromGithubApi(array $issue, $type)
    {
        $this->number = $issue['number'];
        $this->type = $type;
        $this->status = $issue['state'];
        $this->openAt = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $issue['created_at'], new \DateTimeZone('UTC'));
        $this->openAt->setTime(0, 0, 0);
        if ($issue['closed_at']) {
            $this->closeAt = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $issue['closed_at'], new \DateTimeZone('UTC'));
            $this->closeAt->setTime(0, 0, 0);
        }
    }

    public function isOpenOn(\Datetime $day)
    {
        if ($day < $this->openAt) {
            return false;
        }

        if (null === $this->closeAt) {
            return true;
        }

        if ($day < $this->closeAt) {
            return true;
        }

        return false;
    }

    public function isOpenedOn(\Datetime $day)
    {
        return $day == $this->openAt;
    }

    public function isClosedOn(\Datetime $day)
    {
        if (!$this->closeAt) {
            return false;
        }

        return $day == $this->closeAt;
    }

    public function getOpenAt()
    {
        return $this->openAt;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function isPullRequest()
    {
        return 'pull_request' === $this->type;
    }

    public function __toString()
    {
        return sprintf('%s #%d %s (%s -> %s)', $this->type, $this->number, $this->status, $this->openAt->format('Y-m-d'), $this->closeAt ? $this->closeAt->format('Y-m-d') : '-');
    }
}
