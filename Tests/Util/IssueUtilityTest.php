<?php

namespace Lyrixx\GithubGraph\Tests\Util;

use Lyrixx\GithubGraph\Model\Issue;
use Lyrixx\GithubGraph\Util\IssueUtility;

class IssueUtilityTest extends \PHPUnit_Framework_TestCase
{
    private $issueUtility;

    public function setUp()
    {
        $graphiteApi = $this->getMockBuilder('Lyrixx\GithubGraph\Graphite\Api')->disableOriginalConstructor()->getMock();

        $this->issueUtility = new IssueUtility($graphiteApi);
    }

    public function testCreateHistory()
    {
        $issues = array();
        $issues[0] = $this->createIssue('2013/09/06 12:00:00', '2013/09/07 12:00:00');
        $issues[1] = $this->createIssue('2013/09/07 12:00:00', '2013/09/08 12:00:00');
        $issues[2] = $this->createIssue('2013/09/07 12:00:00', '2013/09/10 12:00:00');
        $issues[3] = $this->createIssue('2013/09/08 09:00:00', '2013/09/08 10:00:00');
        $issues[4] = $this->createIssue('2013/09/09 12:00:00');

        $history = $this->issueUtility->createHistory($issues);
        $i = 0;
        foreach ($history as $day => $issuesCollection) {
            if (0 == $i) {
                $this->assertSame('2013/09/06', $day->format('Y/m/d'));
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
            } elseif (1 == $i) {
                $this->assertSame('2013/09/07', $day->format('Y/m/d'));
                $this->assertSame(array(
                    'issues' => 3,
                    'issuesOpen' => 3,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 2,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 1,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (2 == $i) {
                $this->assertSame('2013/09/08', $day->format('Y/m/d'));
                $this->assertSame(array(
                    'issues' => 4,
                    'issuesOpen' => 3,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 1,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 2,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } elseif (3 == $i) {
                $this->assertSame('2013/09/09', $day->format('Y/m/d'));
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
            } elseif (4 == $i) {
                $this->assertSame('2013/09/10', $day->format('Y/m/d'));
                $this->assertSame(array(
                    'issues' => 5,
                    'issuesOpen' => 2,
                    'prs' => 0,
                    'prsOpen' => 0,
                    'nbIssuesOpenedByDay' => 0,
                    'nbPrsOpenedByDay' => 0,
                    'nbIssuesClosedByDay' => 1,
                    'nbPrsClosedByDay' => 0,
                ), $issuesCollection->count());
            } else {
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

    public function tearDown()
    {
        $this->issueUtility = null;
    }

    private function createIssue($createdAt, $closedAt = null)
    {
        $issue = new Issue();
        $issue->mapFromGithubApi(array(
            'number' => null,
            'pull_request' => array('html_url'=> null),
            'state' => null,
            'created_at' => $createdAt,
            'closed_at' => $closedAt,
        ), 'repo');

        return $issue;
    }
}
