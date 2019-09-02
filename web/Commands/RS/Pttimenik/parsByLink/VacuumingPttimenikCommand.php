<?php

namespace Commands\RS\Pttimenik\parsByLink;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingPttimenikCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-27')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));
        $totalRecords = $this->getTotalRecords($crawler);

        for ($i = 0; $i < $totalRecords; $i++){
            $result =
                trim($this->getCompanyName($crawler, $i)) . '}##{' .
                trim($this->getStreet($crawler, $i)) . '}##{' .
                trim($this->getCity($crawler, $i)) . '}##{' .
                trim($this->getPostal($crawler, $i)) . '}##{' .
                trim($this->getPhone($crawler, $i)) . '}##{' . PHP_EOL;

            var_dump($result);
            $this->writeToFile($result);
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
    */
    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return (int)$crawler->filter('.lista-firmi-h2')->count();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        try{
            return $crawler->filter('.lista-firmi-h2')->eq($k)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getStreet(Crawler $crawler, int $k) : string
    {
        try{
            return $crawler->filter('.firma-adresa > p')->eq($k)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getPostal(Crawler $crawler, int $k) : string
    {
        try{
            $postal = preg_replace('/[\D]/', '', $crawler->filter('.firma-postanski')->eq($k)->filter('span')->eq(0)->text());
            if(is_numeric($postal)){
                return $postal;
            }
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCity(Crawler $crawler, int $k) : string
    {
        try{
            $city = $crawler->filter('.firma-postanski')->eq($k)->filter('span')->eq(1)->text();
            return str_replace(',', '', $city);
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getPhone(Crawler $crawler, int $k) : string
    {
        try{
            return preg_replace('/[\D]/', '', $crawler->filter('.firma-telefon > p')->text());
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param string $str
     *
     * Writes to file
     */
    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed8.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}