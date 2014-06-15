GithubGraph
===========

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cc494548-74b1-43e1-bbb6-1ca053552123/mini.png)](https://insight.sensiolabs.com/projects/cc494548-74b1-43e1-bbb6-1ca053552123)

This little app graph all Github history for a project, and push some data to a
graphite server.

Requirements
------------

* php 5.5+
* a graphite server

Installation (phar)
-------------------

Download the latest [phar](https://github.com/lyrixx/GithubGraph/releases), then
create a `config.yml` file. See the [sample](https://github.com/lyrixx/GithubGraph/blob/master/config.yml-dist).

Finally use it:

    php github-graph.phar symfony/symfony

Installation (manual)
---------------------

    composer create-project lyrixx/github-graph
    cd github-graph
    cp config.yml-dist config.yml
    # configure this file
    php bin/github-graph symfony/symfony

You will probably need to update `storage-schemas.conf` of the carbon configuration:

    [github]
    pattern = ^github
    retentions = 1d:5y
