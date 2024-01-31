<?php

namespace App\Classes;

use App\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//#[\Symfony\Component\DependencyInjection\Attribute\Exclude]
abstract class LiveTradesCommandBase extends Command
{
    protected ?OutputInterface $output = null;

    private bool $needsLn = false;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        return Command::INVALID;
    }


    //---

    protected function write(string $line, bool $prefix): void
    {
        $this->output->write((!$prefix ? '' : Utils::dateTimeUTC() . ' ') . $line);
        $this->needsLn = true;
    }

    protected function writeln(string $line): void
    {
        if ($this->needsLn) $this->output->writeln('');
        if (!$line) $this->output->writeln('');
        else $this->output->writeln(Utils::dateTimeUTC() . ' ' . $line);
        $this->needsLn = false;
    }
}
