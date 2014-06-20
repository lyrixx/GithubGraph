<?php

namespace Lyrixx\GithubGraph\Github;

use Doctrine\Common\Cache\Cache;
use Github\Client;
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

    public function getIssues($organisation, $repositoryName, $state, ReportBuilder $reportBuilder = null)
    {
        $reportBuilder and $reportBuilder->comment(sprintf('Download %s issues', $state));

        $parameters['per_page'] = 100;
        $parameters['state'] = $state;
        $cacheId = sprintf('issues-%s-%s-%s', $organisation, $repositoryName, md5(serialize($parameters)));
        if ($issues = $this->cache->contains($cacheId)) {
            return $this->cache->fetch($cacheId);
        }

        $issueApi = $this->client->api('issue');
        $issues = $issueApi->all($organisation, $repositoryName, $parameters);

        $pagination = $this->client->getHttpClient()->getLastResponse()->getPagination();
        if (!$pagination) {
            $this->cache->save($cacheId, $issues);

            return $issues;
        }

        $nbPages = $this->getPageParameter($pagination['last']);

        $reportBuilder and $reportBuilder->comment(sprintf('%d page to download', $nbPages));
        $reportBuilder and $reportBuilder->startProgress($nbPages);
        $reportBuilder and $reportBuilder->advanceProgress();
        $page = 2;
        do {
            $parameters['page'] = $page;
            $issues = array_merge($issues, $issueApi->all($organisation, $repositoryName, $parameters));
            $pagination = $this->client->getHttpClient()->getLastResponse()->getPagination();

            $reportBuilder and $reportBuilder->advanceProgress();

            if (!isset($pagination['next'])) {
                break;
            }
            $page = $this->getPageParameter($pagination['next']);
        } while (true);

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
