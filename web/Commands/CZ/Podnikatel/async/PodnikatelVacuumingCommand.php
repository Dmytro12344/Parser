<?php


namespace Commands\CZ\Podnikatel\async;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

class PodnikatelVacuumingCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cz:vacuuming-31')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $guzzle = new GuzzleWrap();

        try {
            $crawler = new Crawler($guzzle->getContent($input->getOption('url')));
            $totalRecords = $this->getTotalRecords($crawler);

            for ($i = 0; $i < $totalRecords; $i++) {

                $result = array_values([
                    'category' => trim($this->getCategory($crawler)),
                    'name' => trim($this->getCompanyName($crawler, $i)),
                    'address' => trim($this->getStreet($crawler, $i)),
                    'postal' => trim($this->getPostal($crawler, $i)),
                    'city' => trim($this->getCity($crawler, $i)),
                    'phone' => '',
                    'email' => '',
                    'site' => '',
                ]);

                var_dump($result);
                $this->writeToFile([$result]);
            }
        }catch(\Exception $e){
            echo $e->getMessage();
        }
    }

    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return $crawler->filterXPath("//input[@id='search-field-query']")->attr('value');
        }catch (\Exception $e){
            return '';
        }
    }


    protected function getCompanyName(Crawler $crawler, int $k): string
    {
        try {
            return $crawler->filter('.list__subheading > a')->eq($k)->text();
        } catch (\Exception $e) {
            return '';
        }
    }


    protected function getStreet(Crawler $crawler, int $k) : string
    {
        try {
            return $crawler->filterXPath("//span[@itemprop='streetAddress']")->eq($k)->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCity(Crawler $crawler, int $k) : string
    {
        try {
            return $crawler->filterXPath("//span[@itemprop='addressLocality']")->eq($k)->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPostal(Crawler $crawler, int $k) : string
    {
        try {
            return $crawler->filterXPath("//span[@itemprop='postalCode']")->eq($k)->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPhone(Crawler $crawler, int $k) : string
    {
    }

    protected function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.list__subheading > a')->count();
    }

    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed2.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}