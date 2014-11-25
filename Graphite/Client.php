<?php

namespace Lyrixx\GithubGraph\Graphite;

class Client
{
    private $prefix;
    private $host;
    private $port;
    private $protocol;
    private $data;

    public function __construct($prefix = 'github.graph.', $host = '127.0.0.1', $port = 2003, $protocol = 'tcp')
    {
        $this->prefix = $prefix;
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->data = array();
    }

    public function push($stat, $value, $time = null)
    {
        $this->data[] = sprintf("%s %d %d\n", $this->prefix.$stat, $value, $time ?: time());
    }

    public function flush()
    {
        if (!$this->data) {
            return;
        }

        $fp = @fsockopen($this->protocol . '://' . $this->host, $this->port);

        if (!$fp) {
            throw new \RuntimeException(sprintf('Impossible to connect graphite server on "%s://%s:%s"', $this->protocol, $this->host, $this->port));
        }

        foreach ($this->data as $line) {
            fwrite($fp, $line);
        }

        fclose($fp);

        $this->data = array();
    }
}
