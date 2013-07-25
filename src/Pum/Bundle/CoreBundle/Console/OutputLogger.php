<?php

namespace Pum\Bundle\CoreBundle\Console;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\OutputInterface;

class OutputLogger extends AbstractLogger
{
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function log($level, $message, array $context = array())
    {
        $this->output->writeln(sprintf("<info>[%s]</info> %s <comment>%s</comment>", $level, $message, empty($context) ? '' : json_encode($context)));
    }
}
