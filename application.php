<?php


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();


/** System Commands */
$application->add(new Commands\Core\CreateParserCommand());

$application->add(new Commands\CreateStartCommand());
$application->add(new Commands\VacuumingCommand());

$application->add(new Commands\TwoSteps\StartProcessCommand());
$application->add(new Commands\TwoSteps\MainContentCommand());
$application->add(new Commands\TwoSteps\ProfileProcessCommand());





/** CZ */

/** https://www.zlatestranky.cz */
$application->add(new Commands\CZ\Zlatestranky\ZlatestrankyParserCommand());

/** https://www.obchodnirejstrikfirem.cz */
$application->add(new Commands\CZ\Obchodnirejstrikfirem\ObchodnirejstrikfiremParserCommand());
$application->add(new Commands\CZ\Obchodnirejstrikfirem\ObchodnirejstrikfiremProfileCommand());

/** https://obchody.sluzby.cz */
$application->add(new  Commands\CZ\Sluzby\asyncWithProfile\SluzbyParserCommand());
$application->add(new  Commands\CZ\Sluzby\asyncWithProfile\MainContentCommand());
$application->add(new  Commands\CZ\Sluzby\asyncWithProfile\VacuumingProfileCommand());

/** http://www.najisto.centrum.com */
$application->add(new  Commands\CZ\Najisto\parsByCategories\NajistoParserCommand());
$application->add(new  Commands\CZ\Najisto\parsByCategories\VacuumingNajistoCommand());


/** https://www.zivefirmy.cz */
$application->add(new Commands\CZ\Zivefirmy\asinc\ZivefirmyParserCommand());
$application->add(new Commands\CZ\Zivefirmy\asinc\MainContentCommand());
$application->add(new Commands\CZ\Zivefirmy\asinc\VacuumingProfileContent());

$application->add(new Commands\CZ\Zivefirmy\notAsinc\ZivefirmyParserCommand());


/** https://www.csfirmy.cz */
$application->add(new Commands\CZ\Csfirmy\asinc\CsfirmyParserCommand());
$application->add(new Commands\CZ\Csfirmy\notAsinc\CsfirmyParserCommand());

/** http://www.infoaktualne.cz */
$application->add(new  Commands\CZ\Infoaktualne\parsByCategories\InfoaktualneParserCommand());
$application->add(new  Commands\CZ\Infoaktualne\parsByCategories\VacuumingInfoaktualneCommand());

/** https://www.podnikatel.cz */
$application->add(new Commands\CZ\Podnikatel\async\PodnikatelParserCommand());
$application->add(new Commands\CZ\Podnikatel\async\PodnikatelVacuumingCommand());

/** https://rejstrik-firem.kurzy.cz */
$application->add(new Commands\CZ\Rejstrik\async\RejstrikParserCommand());
$application->add(new Commands\CZ\Rejstrik\async\RejstrikVacuumingCommand());





/** RS */

/** http://www.privredni-imenik.com */
$application->add(new Commands\RS\Privredni\async\PrivredniParserCommand());
$application->add(new Commands\RS\Privredni\async\VacuumingProfileCommand());

/** https://www.biznisgroup.rs */
$application->add(new Commands\RS\Biznesgroup\async\BiznesgroupParserCommand());
$application->add(new Commands\RS\Biznesgroup\async\MainContentCommand());
$application->add(new Commands\RS\Biznesgroup\async\VacuumingProfileCommand());

/** https://www.biznisgroup.rs */
$application->add(new Commands\RS\Companywall\asyncWithProfile\CompanywallParserCommand());
$application->add(new Commands\RS\Companywall\asyncWithProfile\VacuumingCompanywallCommand());




/** PL */

/** https://www.pkt.pl */
$application->add(new Commands\PL\Ptc\asyncWithProfile\PktParserCommand());
$application->add(new Commands\PL\Ptc\asyncWithProfile\MainContentCommand());
$application->add(new Commands\PL\Ptc\asyncWithProfile\VacuumingProfileCommand());





$application->run();

