<?php


namespace Commands\CZ\Podnikatel\async;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class PodnikatelParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('cz:start-31')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/CZ/Podnikatel/async/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        $url = 'https://www.podnikatel.cz/rejstrik/vyhledavani/?query=';

        foreach($categories as $key => $category){
            try {

                $link = $url . urldecode(trim($category));
                $process = new Process("php application.php cz:vacuuming-31 --url='$link'");
                $process->start();
                $activeProcess[] = $process;
                var_dump("$key link is processed");

                /** Cleaning memory of useless processes */
                $this->processControl($activeProcess);

                if($key === count($categories) -1){
                    sleep(60);
                }
            } catch (ProcessFailedException $e) {
                $output->writeln([
                    $e->getMessage(),
                ]);
            }
        }
    }


    /**
     * @param $processes
     * Method that cleans memory from useless processes
     */
    public function processControl($processes) : void
    {
        if(count($processes) >= 20){
            while(count($processes) >= 20){
                foreach($processes as $key => $runningProcess){
                    if(!$runningProcess->isRunning()){
                        unset($processes[$key]);
                    }
                }
                sleep(1);
            }
        }
    }
}