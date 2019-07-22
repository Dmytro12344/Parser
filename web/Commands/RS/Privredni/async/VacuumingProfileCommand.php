<?php


namespace Commands\RS\Privredni\async;


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
        $this->setName('rs:vacuuming-1')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
         $guzzle = new GuzzleWrap();
         $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

         for($k=0; $k < $this->getTotalRecords($crawler); $k++) {

             $result =
                 trim($this->getCompanyName($crawler, $k)) . '}##{' .
                 trim($this->getStreet($crawler, $k)) . '}##{' .
                 trim($this->getCity($crawler, $k)) . '}##{' .
                 trim($this->getPostal($crawler, $k)) . '}##{' .
                 trim($this->getPhone($crawler, $k)) . "\n";

             var_dump($result);
             $this->writeToFile($result);
         }
    }


    protected function getCompanyName(Crawler $crawler, int $k): string
    {
        try {
            return $crawler->filter('.title > a')->eq($k)->text();
        } catch (\Exception $e) {
            return '';
        }
    }


    protected function getStreet(Crawler $crawler, int $k) : string
    {
        try {
            $filter = $crawler->filter('.jobs-item')->eq($k)->filter('.description')->eq(0)->html();

            if (strpos($filter, '<br>')) {
                $street = explode('<br>', $filter);
                return $street[1];
            }

            if (!strrpos($filter, '<br>')) {
                $street = explode(' ', $filter);
                if (!is_numeric($street[0])) {
                    return $filter;
                }
            }

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCity(Crawler $crawler, int $k) : string
    {
        try {
            $filter = $crawler->filter('.jobs-item')->eq($k)->filter('.description')->eq(0)->html();
            $currentCity = '';

            if (strpos($filter, '<br>')) {
                $description = explode('<br>', $filter);
                $postalANDcity = $description[0];

                if (strpos($postalANDcity, ' ')) {
                    $city = explode(' ', $postalANDcity);

                    if (is_numeric($city[0])) {
                        for ($i = 1; $i < @count($city); $i++) {
                            $currentCity .= $city[$i] . ' ';
                        }
                        return $currentCity;
                    }

                    for ($i = 0; $i < @count($city); $i++) {
                        $currentCity .= $city[$i] . ' ';
                    }
                    return $currentCity;
                }
            }

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPostal(Crawler $crawler, int $k) : string
    {
        try {
            $filter = $crawler->filter('.jobs-item')->eq($k)->filter('.description')->eq(0)->html();

            if (strpos($filter, '<br>')) {
                $description = explode('<br>', $filter);
                $postalANDcity = $description[0];

                if (strpos($postalANDcity, ' ')) {
                    $postal = explode(' ', $postalANDcity);

                    if (is_numeric($postal[0])) {
                        return $postal[0];
                    }

                }
            }

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPhone(Crawler $crawler, int $k) : string
    {
        try {
            $filter
                = $crawler->filter('.jobs-item')->eq($k)->filter('.description')->eq(1)->html();

            if (strpos($filter, '<br><br>')) {
                $phone = explode('<br><br>', $filter);
                if (strpos($phone[1], '<br>')) {
                    $currentPhone = explode('<br>', $phone[1]);

                    if (strpos($currentPhone[0], ':')) {
                        $neededPhone = explode(':', $currentPhone[0]);
                        $neededPhone[1] = str_replace([' ', '(', ')', '-'], '', $neededPhone[1]);
                        return $neededPhone[1];
                    }
                }

                if (strpos($phone[1], ':')) {
                    $currentPhone = explode(':', $phone[1]);
                    if (strpos($currentPhone[1], '<h6>')) {
                        $neededPhone = explode('<h6>', $currentPhone[1]);
                        $neededPhone[0] = str_replace([' ', '(', ')', '-'], '', $neededPhone[0]);
                        return $neededPhone[0];
                    }

                }
            }

            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.jobs-item')->count();
    }

    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}