<?php

namespace Lyrixx\GithubGraph\Tests\Util;

use Lyrixx\GithubGraph\Model\Object;
use Lyrixx\GithubGraph\Util\ObjectUtility;

class ObjectUtilityTest extends \PHPUnit_Framework_TestCase
{
    private $objectUtility;

    protected function setUp()
    {
        $graphiteClient = $this->getMockBuilder('Lyrixx\GithubGraph\Graphite\Client')->disableOriginalConstructor()->getMock();

        $this->objectUtility = new ObjectUtility($graphiteClient);
    }

    protected function tearDown()
    {
        $this->objectUtility = null;
    }

    public function testCreateHistory()
    {
        $issues = array();
        $issues[0] = $this->createIssue('2013-09-06T12:00:00Z', '2013-09-07T12:00:00Z');
        $issues[1] = $this->createIssue('2013-09-07T12:00:00Z', '2013-09-08T12:00:00Z');
        $issues[2] = $this->createIssue('2013-09-07T12:00:00Z', '2013-09-10T12:00:00Z');
        $issues[3] = $this->createIssue('2013-09-08T09:00:00Z', '2013-09-08T10:00:00Z');
        $issues[4] = $this->createIssue('2013-09-09T12:00:00Z');

        $history = $this->objectUtility->createHistory($issues);
        $i = 0;
        foreach ($history as $day => $issuesCollection) {
            if (0 == $i) {
                $this->assertSame('2013-09-05', $day->format('Y-m-d'));
                $this->assertSame(array(
                    'issues' => 0,
                    'issuesOpen' => 0,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 0,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 0,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (1 == $i) {
                $this->assertSame('2013-09-06', $day->format('Y-m-d'));
                $this->assertSame(array(
                    'issues' => 1,
                    'issuesOpen' => 1,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 1,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 0,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (2 == $i) {
                $this->assertSame('2013-09-07', $day->format('Y-m-d'));
                $this->assertSame(array(
                    'issues' => 3,
                    'issuesOpen' => 2,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 2,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 1,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (3 == $i) {
                $this->assertSame('2013-09-08', $day->format('Y-m-d'));
                $this->assertSame(array(
                    'issues' => 4,
                    'issuesOpen' => 1,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 1,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 2,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (4 == $i) {
                $this->assertSame('2013-09-09', $day->format('Y-m-d'));
                $this->assertSame(array(
                    'issues' => 5,
                    'issuesOpen' => 2,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 1,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 0,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (5 == $i) {
                $this->assertSame('2013-09-10', $day->format('Y-m-d'));
                $this->assertSame(array(
                    'issues' => 5,
                    'issuesOpen' => 1,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 0,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 1,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (6 == $i) {
                $this->assertSame(array(
                    'issues' => 5,
                    'issuesOpen' => 1,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 0,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 0,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            }
            $i++;
        }
        $this->assertSame((new \DateTime('tomorrow'))->format('Y-m-d'), $day->format('Y-m-d'));
    }

    private function createIssue($createdAt, $closedAt = null)
    {
        $issue = new Object();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'state' => null,
            'created_at' => $createdAt,
            'closed_at' => $closedAt,
        ), 'issue');

        return $issue;
    }
}
