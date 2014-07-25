<?php

namespace Lyrixx\GithubGraph\Console\Report;

use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ReportBuilder
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class ReportBuilder
{
    private $output;

    private $progress;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function comment($message)
    {
        if (OutputInterface::VERBOSITY_VERBOSE > $this->output->getVerbosity()) {
            return;
        }

        $this->output->writeln(sprintf('<comment>%s</comment>', $message));
    }

    public function startProgress($steps, $redrawFrequency = 1)
    {
        if (OutputInterface::VERBOSITY_VERY_VERBOSE > $this->output->getVerbosity()) {
            return;
        }

        $this->progress = new ProgressHelper();

        $this->progress->setRedrawFrequency($redrawFrequency);

        $this->progress->start($this->output, $steps);
    }

    public function advanceProgress()
    {
        if (OutputInterface::VERBOSITY_VERY_VERBOSE > $this->output->getVerbosity()) {
            return;
        }

        if (null === $this->progress) {
            throw new \LogicException('Progress has not been started.');
        }

        $this->progress->advance();
    }

    public function endProgress()
    {
        if (OutputInterface::VERBOSITY_VERY_VERBOSE > $this->output->getVerbosity()) {
            return;
        }

        if (null === $this->progress) {
            throw new \LogicException('Progress has not been started.');
        }

        $this->progress->display(true);
        $this->progress->finish();
        $this->progress = null;
    }
}
