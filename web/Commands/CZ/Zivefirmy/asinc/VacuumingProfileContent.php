<?php


namespace Commands\CZ\Zivefirmy\asinc;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

class VacuumingProfileContent extends Command
{
    protected function configure(): void
    {
        $this->setName('cz:vacuuming-11')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        $result = array_values([
            'category' => trim($this->getCategory($crawler)),
            'name' => trim($this->getCompanyName($crawler)),
            'street' => trim($this->getAddress($crawler)),
            'postal' => trim($this->getPostal($crawler)),
            'city' => trim($this->getCity($crawler)),
            'phone' => trim($this->getPhone($crawler)),
            'email' => trim($this->getEmail($crawler)),
            'site' => trim($this->getSite($crawler)),
        ]);

        var_dump($result);
        $this->writeToFile([$result]);
    }

    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.part > .pack > .cloud > h2')->text();
        } catch(\Exception $e){
            return '';
        }
    }

    protected function getCompanyName(Crawler $crawler) : string
    {
        try {
            return $crawler->filterXPath("//h1[@itemprop='name']")->eq(0)->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPhone(Crawler $crawler) : string
    {
        try {
            if ($crawler->filterXPath("//span[@itemprop='telephone']")->count() > 0) {
                return $crawler->filterXPath("//span[@itemprop='telephone']")->text();
            }

            if ($crawler->filterXPath("//span[@itemprop='telephone']")->filter('font > font')->count() > 0) {
                return $crawler->filterXPath("//span[@itemprop='telephone']")->filter('font > font')->text();
            }
            return '';
        } catch(\Exception $e){
            return '';
        }
    }

    protected function getEmail(Crawler $crawler) : string
    {
        try {
            return $crawler->filterXPath("//a[@itemprop='email']")->text();
        } catch(\Exception $e){
            return '';
        }
    }

    protected function getSite(Crawler $crawler) : string
    {
        try {
            return $crawler->filterXPath("//span[@class='title']")->text();
        } catch(\Exception $e){
            return '';
        }
    }

    protected function getAddress(Crawler $crawler) : string
    {
        try {
            return $crawler->filterXPath("//span[@itemprop='streetAddress']")->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPostal(Crawler $crawler) : string
    {
        try {
            return $crawler->filterXPath("//span[@itemprop='postalCode']")->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getCity(Crawler $crawler) : string
    {
        try{
            return $crawler->filterXPath("//span[@itemprop='addressLocality']")->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed5.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}