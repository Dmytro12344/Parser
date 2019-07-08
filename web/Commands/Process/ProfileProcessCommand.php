<?php


namespace Commands\Process;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Wraps\Process\MainContent;


class ProfileProcessCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:download-profile-content')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pars = new MainContent();


        $output->writeln([
           $pars->getProfileContent()
        ]);

    }
}