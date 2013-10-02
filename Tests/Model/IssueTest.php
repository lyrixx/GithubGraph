<?php

namespace Lyrixx\GithubGraph\Tests\Model;

use Lyrixx\GithubGraph\Model\Issue;

class IssueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsOpenClose()
    {
        $issue = new Issue();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'pull_request' => array('html_url'=> null),
            'state' => null,
            'created_at' => '2010/06/06 12:00:00',
            'closed_at' => '2010/06/06 14:00:00',
        ), 'repo');

        $this->assertFalse($issue->isOpenAtDay(new \Datetime('2010/06/05')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/05')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime('2010/06/06')));
        $this->assertTrue($issue->isOpenedAtDay(new \Datetime('2010/06/06')));
        $this->assertFalse($issue->isOpenAtDay(new \Datetime('2010/06/07')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/07')));

        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/05')));
        $this->assertTrue($issue->isClosedAtDay(new \Datetime('2010/06/06')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/07')));

        $issue = new Issue();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'pull_request' => array('html_url'=> null),
            'state' => null,
            'created_at' => '2010/06/06 12:00:00',
            'closed_at' => '2010/06/08 14:00:00',
        ), 'repo');

        $this->assertFalse($issue->isOpenAtDay(new \Datetime('2010/06/05')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/05')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime('2010/06/06')));
        $this->assertTrue($issue->isOpenedAtDay(new \Datetime('2010/06/06')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime('2010/06/07')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/07')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime('2010/06/08')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/08')));
        $this->assertFalse($issue->isOpenAtDay(new \Datetime('2010/06/09')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/09')));

        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/05')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/06')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/07')));
        $this->assertTrue($issue->isClosedAtDay(new \Datetime('2010/06/08')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/09')));

        $issue = new Issue();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'pull_request' => array('html_url'=> null),
            'state' => null,
            'created_at' => '2010/06/06 12:00:00',
            'closed_at' => null,
        ), 'repo');

        $this->assertFalse($issue->isOpenAtDay(new \Datetime('2010/06/05')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/05')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime('2010/06/06')));
        $this->assertTrue($issue->isOpenedAtDay(new \Datetime('2010/06/06')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime('2010/06/07')));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime('2010/06/07')));
        $this->assertTrue($issue->isOpenAtDay(new \Datetime()));
        $this->assertFalse($issue->isOpenedAtDay(new \Datetime()));

        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/05')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/06')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/07')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/08')));
        $this->assertFalse($issue->isClosedAtDay(new \Datetime('2010/06/09')));
    }
}
