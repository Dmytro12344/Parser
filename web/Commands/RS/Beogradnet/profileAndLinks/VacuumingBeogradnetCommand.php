<?php

namespace Commands\RS\Beogradnet\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingBeogradnetCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-7')
            ->setDescription('Starts download from')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        $phone = trim($this->getPhone($crawler));

            if($phone !== '') {
                $result =
                    trim($this->getCompanyName($crawler)) . '}##{' .
                    trim($this->getStreet($crawler)) . '}##{' .
                    trim($this->getCity($crawler)) . '}##{' .
                    trim($this->getPostal($crawler)) . '}##{' .
                    $phone . "\n";

                var_dump($result);
                $this->writeToFile($result);
            }

    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.wlt_shortcode_TITLE')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getStreet(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.val_adresa')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPostal(Crawler $crawler) : string
    {
        try{
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCity(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.val_opstina')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPhone(Crawler $crawler) : string
    {
        try{
            return str_replace(['/', ' ', '+', ')', '(', '-', '_'], '', $crawler->filter('.val_telephone')->text());
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getEmail(Crawler $crawler) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getSite(Crawler $crawler) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param string $str
     * Writes to file
    */
    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed5.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}