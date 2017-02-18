<?php


//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Sat Feb 18 08:44:15 WET 2017
//


namespace OussamaElgoumri\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class CreateEnv extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:create')
            ->addOption('example', null, InputOption::VALUE_NONE, 'Generate .env.example file')
            ->setDescription('Create .env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $e = base_path('.env');
        $ee = base_path('.env.example');

        if ($input->getOption('example')) {
            copy($e, $ee);
            `sed -i 's/=.*$/=/g' {$ee}`;
            $output->writeln('.env.example file is generated');
        } else {
            copy($ee, $e);
            $output->writeln('.env file is created');
        }
    }
}
