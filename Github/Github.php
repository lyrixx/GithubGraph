<?php

namespace Lyrixx\GithubGraph\Github;

use Doctrine\Common\Cache\Cache;
use Github\Client;
use Github\ResultPager;
use Lyrixx\GithubGraph\Console\Report\ReportBuilder;

class Github
{
    private $client;
    private $cache;

    public function __construct(Client $client, Cache $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function getRepositoryInformation($organisation, $repositoryName)
    {
        $repo = $this->client->api('repo');

        return $repo->show($organisation, $repositoryName);
    }

    public function getIssues($organisation, $repositoryName, ReportBuilder $reportBuilder = null)
    {
        $issues = $this->getObjects($organisation, $repositoryName, 'issue', $reportBuilder);

        // Note: Every pull request is an issue, but not every issue is a pull
        // request. If the issue is not a pull request, the response omits the
        // pull_request attribute.
        $issues = array_filter($issues, function ($issue) {
            return !isset($issue['pull_request']['url']);
        });

        return $issues;
    }

    public function getPullRequests($organisation, $repositoryName, ReportBuilder $reportBuilder = null)
    {
        return $this->getObjects($organisation, $repositoryName, 'pull_request', $reportBuilder);
    }

    private function getObjects($organisation, $repositoryName, $type, ReportBuilder $reportBuilder = null)
    {
        $parameters = array();
        $parameters['per_page'] = 100;
        $parameters['state'] = 'all';
        $cacheId = sprintf('%s-%s-%s-%s', $organisation, $repositoryName, $type, md5(json_encode($parameters)));
        if ($issues = $this->cache->contains($cacheId)) {
            return $this->cache->fetch($cacheId);
        }

        $paginator = new ResultPager($this->client);
        $issues = $paginator->fetch($this->client->api($type), 'all', array($organisation, $repositoryName, $parameters));

        $pagination = $paginator->getPagination();
        if (!$pagination) {
            $this->cache->save($cacheId, $issues);

            return $issues;
        }

        $nbPages = $this->getPageParameter($pagination['last']);

        $reportBuilder and $reportBuilder->comment(sprintf('%d page to download', $nbPages));
        $reportBuilder and $reportBuilder->startProgress($nbPages);
        $reportBuilder and $reportBuilder->advanceProgress();
        while ($paginator->hasNext()) {
            $issues = array_merge($issues, $paginator->fetchNext());
            $reportBuilder and $reportBuilder->advanceProgress();
        }

        $reportBuilder and $reportBuilder->endProgress();

        $this->cache->save($cacheId, $issues);

        return $issues;
    }

    private function getPageParameter($url)
    {
        $url = parse_url($url);
        parse_str($url['query'], $qsa);

        return $qsa['page'];
    }
}
