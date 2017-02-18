<?php

//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 14:06:10 WET 2017
//

namespace OussamaElgoumri\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class Uptobox extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('link')
            ->setDescription('Uptobox link to process');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        var_dump($input->getArgument('link'));
    }
}
