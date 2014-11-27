GithubGraph
===========

This little app graph all Github history for a project, and push some data to a
[graphite server](http://graphite.wikidot.com/).

Requirements
------------

* php 5.5+
* a graphite server

Installation (phar)
-------------------

Download the latest [phar](https://github.com/lyrixx/GithubGraph/releases), then
create a `config.yml` file. See the [sample](https://github.com/lyrixx/GithubGraph/blob/master/config.yml-dist).

Installation (manual)
---------------------

    composer create-project lyrixx/github-graph
    cd github-graph
    cp config.yml-dist config.yml
    # configure this file
    php bin/github-graph symfony/symfony

Graphite configuration
----------------------

You will probably need to update `storage-schemas.conf` of the carbon configuration:

    [github]
    pattern = ^github
    retentions = 1d:5y

Usage
-----

    php github-graph.phar symfony/symfony

you can also play with verbosity level:

    # display nothing
    php github-graph.phar symfony/symfony -q

    # default display
    php github-graph.phar symfony/symfony

    # More verbose
    php github-graph.phar symfony/symfony -v

    # Very verbose
    php github-graph.phar symfony/symfony -vv
