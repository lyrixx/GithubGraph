<?php

namespace Lyrixx\GithubGraph\Tests\Model;

use Lyrixx\GithubGraph\Model\Object;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testIsOpenClose()
    {
        $issue = new Object();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'state' => null,
            'created_at' => '2010-06-06T12:00:00Z',
            'closed_at' => '2010-06-06T14:00:00Z',
        ), 'pull_request');

        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenedOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));

        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isClosedOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));

        $issue = new Object();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'pull_request' => array('html_url'=> null),
            'state' => null,
            'created_at' => '2010-06-06T12:00:00Z',
            'closed_at' => '2010-06-08T14:00:00Z',
        ), 'pull_request');

        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenedOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-08', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-08', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-09', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-09', new \DateTimeZone('UTC'))));

        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isClosedOn(new \Datetime('2010-06-08', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-09', new \DateTimeZone('UTC'))));

        $issue = new Object();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'state' => null,
            'created_at' => '2010-06-06T12:00:00Z',
            'closed_at' => null,
        ), 'pull_request');

        $this->assertFalse($issue->isOpenOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenedOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertTrue($issue->isOpenOn(new \Datetime('now', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isOpenedOn(new \Datetime('now', new \DateTimeZone('UTC'))));

        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-05', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-06', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-07', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-08', new \DateTimeZone('UTC'))));
        $this->assertFalse($issue->isClosedOn(new \Datetime('2010-06-09', new \DateTimeZone('UTC'))));
    }
}
