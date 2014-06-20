<?php

namespace Lyrixx\GithubGraph\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends SymfonyApplication
{
    public function __construct(\Pimple $container)
    {
        parent::__construct('GithubGraph', 0.2);

        $this->add(new Command\GraphCommand($container['github'], $container['issue.utilily']));
    }

    protected function getCommandName(InputInterface $input)
    {
        return 'graph';
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}

