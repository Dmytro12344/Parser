<?php

namespace Commands\CZ\Obchodnirejstrikfirem;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;


class ObchodnirejstrikfiremProfileCommand extends Command
{
    protected function configure() : void
    {
        $this->setName('start-2-profile')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

         $result = array_values([
             'name' => $this->getCompanyName($crawler),
         ]);



         $this->writeToFile([$result]);


        $output->writeln([
            var_dump($result),
        ]);
    }


    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }

    public function getPostal($fullAddress)
    {
        $results = explode(" ", $fullAddress);

    }



    public function getCity($fullAddress)
    {
        $address = explode(" ", $fullAddress);

    }

    public function getAddress($fullAddress)
    {
        $address = explode(" ", $fullAddress);

    }






}