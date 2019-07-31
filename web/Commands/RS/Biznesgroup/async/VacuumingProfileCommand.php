<?php


namespace Commands\RS\Biznesgroup\async;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

class VacuumingProfileCommand extends Command
{

    protected function configure(): void
    {
        $this->setName('rs:vacuuming-2')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
         $guzzle = new GuzzleWrap();
         $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

         $result =
             trim($this->getCompanyName($crawler)) . '}##{' .
             trim($this->getStreet($crawler)) . '}##{' .
             trim($this->getCity($crawler)) . '}##{' .
             '}##{' .
             trim($this->getPhone($crawler)) . "\n";

             var_dump($result);
             $this->writeToFile($result);
    }


    protected function getCompanyName(Crawler $crawler): string
    {
        try {
            return $crawler->filter('style + h1 > span > strong')->text();
        } catch (\Exception $e) {
            return '';
        }
    }


    protected function getStreet(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filterXPath("//li[@class='address']")->filterXPath("//span[@class='value']")->text();

            if(strpos($filter, ',')){
                $street  = explode(',', $filter);
                return $street[0];
            }

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCity(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filterXPath("//li[@class='address']")->filterXPath("//span[@class='value']")->text();

            if(strpos($filter, ',')){
                $street  = explode(',', $filter);
                return $street[1];
            }

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPostal(Crawler $crawler) : string
    {
        try {

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPhone(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filter('.phone > span > a')->html();
            $filter = preg_replace('/[a-zA-Z]/', '', $filter);

            if(strpos($filter, ')')){
                $filter = explode(')', $filter);
                return str_replace(['/', ')', '(', '-', '+', ' ', '_'], '',$filter[0]);
            }

            $filter = trim(preg_replace('~[^0-9 ]+~', '', $filter));

            if(strpos($filter, ' ')){
                $filter = explode(' ', $filter);
                return $filter[0];
            }
            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed1.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}