<?php


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();


/** System Commands */
$application->add(new Commands\Core\CreateParserCommand());

$application->add(new Commands\RO\Paginari\links\CreateStartCommand());
$application->add(new Commands\RO\Paginari\links\VacuumingCommand());



/** CZ */

/** https://www.zlatestranky.cz */
$application->add(new Commands\CZ\Zlatestranky\ZlatestrankyParserCommand());

/** https://www.obchodnirejstrikfirem.cz */
$application->add(new Commands\CZ\Obchodnirejstrikfirem\ObchodnirejstrikfiremParserCommand());

/** https://obchody.sluzby.cz */
$application->add(new  Commands\CZ\Sluzby\asyncWithProfile\SluzbyParserCommand());
$application->add(new  Commands\CZ\Sluzby\asyncWithProfile\MainContentCommand());
$application->add(new  Commands\CZ\Sluzby\asyncWithProfile\VacuumingProfileCommand());

/** http://www.najisto.centrum.com */
$application->add(new  Commands\CZ\Najisto\profileAndCategories\NajistoParserCommand());
$application->add(new  Commands\CZ\Najisto\profileAndCategories\VacuumingNajistoCommand());
$application->add(new  Commands\CZ\Najisto\profileAndCategories\ProfileLinksCommand());


/** https://www.zivefirmy.cz */
$application->add(new Commands\CZ\Zivefirmy\asinc\ZivefirmyParserCommand());
$application->add(new Commands\CZ\Zivefirmy\asinc\MainContentCommand());
$application->add(new Commands\CZ\Zivefirmy\asinc\VacuumingProfileContent());


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

/** http://www.ceske-hospudky.cz */
$application->add(new  Commands\CZ\Ceske\profileAndLinks\CeskeParserCommand());
$application->add(new  Commands\CZ\Ceske\profileAndLinks\VacuumingCeskeCommand());
$application->add(new  Commands\CZ\Ceske\profileAndLinks\ProfileLinksCommand());

/** https://www.restu.cz */
$application->add(new  Commands\CZ\Restu\profileAndLinks\RestuParserCommand());
$application->add(new  Commands\CZ\Restu\profileAndLinks\VacuumingRestuCommand());
$application->add(new  Commands\CZ\Restu\profileAndLinks\ProfileLinksCommand());





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

/** https://www.moja-delatnost.rs */
$application->add(new  Commands\RS\Moja\parsByLink\MojaParserCommand());
$application->add(new  Commands\RS\Moja\parsByLink\VacuumingMojaCommand());

/** https://mojabaza.com */
$application->add(new  Commands\RS\Mojabaza\profileAndLinks\MojabazaParserCommand());
$application->add(new  Commands\RS\Mojabaza\profileAndLinks\VacuumingMojabazaCommand());
$application->add(new  Commands\RS\Mojabaza\profileAndLinks\ProfileLinksCommand());

/** al */
$application->add(new  Commands\RS\Al\parsByLink\AlParserCommand());
$application->add(new  Commands\RS\Al\parsByLink\VacuumingAlCommand());


/** http://www.Beogradnet.net */
$application->add(new  Commands\RS\Beogradnet\profileAndLinks\BeogradnetParserCommand());
$application->add(new  Commands\RS\Beogradnet\profileAndLinks\VacuumingBeogradnetCommand());
$application->add(new  Commands\RS\Beogradnet\profileAndLinks\ProfileLinksCommand());

/**  */
$application->add(new  Commands\CZ\Tripadivisor\profileAndLinks\TripadivisorParserCommand());
$application->add(new  Commands\CZ\Tripadivisor\profileAndLinks\VacuumingTripadivisorCommand());
$application->add(new  Commands\CZ\Tripadivisor\profileAndLinks\ProfileLinksCommand());

/** http://www.poslovne-strane.co.rs */
$application->add(new  Commands\RS\Poslovne\profileAndLinks\PoslovneParserCommand());
$application->add(new  Commands\RS\Poslovne\profileAndLinks\VacuumingPoslovneCommand());
$application->add(new  Commands\RS\Poslovne\profileAndLinks\ProfileLinksCommand());

/** http://www.imenik.co */
$application->add(new  Commands\RS\Imenik\profileAndLinks\ImenikParserCommand());
$application->add(new  Commands\RS\Imenik\profileAndLinks\VacuumingImenikCommand());
$application->add(new  Commands\RS\Imenik\profileAndLinks\ProfileLinksCommand());

/** https://www.011info.com/ */
$application->add(new  Commands\RS\Info011\profileAndLinks\Info011ParserCommand());
$application->add(new  Commands\RS\Info011\profileAndLinks\VacuumingInfo011Command());
$application->add(new  Commands\RS\Info011\profileAndLinks\ProfileLinksCommand());

/** https://www.navidiku.rs */
$application->add(new  Commands\RS\Navidiku\profileAndLinks\NavidikuParserCommand());
$application->add(new  Commands\RS\Navidiku\profileAndLinks\VacuumingNavidikuCommand());
$application->add(new  Commands\RS\Navidiku\profileAndLinks\ProfileLinksCommand());

/** https://www.poslovnivodic.com */
$application->add(new  Commands\RS\Poslovnivodic\profileAndLinks\PoslovnivodicParserCommand());
$application->add(new  Commands\RS\Poslovnivodic\profileAndLinks\VacuumingPoslovnivodicCommand());
$application->add(new  Commands\RS\Poslovnivodic\profileAndLinks\ProfileLinksCommand());

/** https://www.firmesrbije.rs */
$application->add(new  Commands\RS\Firmesrbije\parsByLink\FirmesrbijeParserCommand());
$application->add(new  Commands\RS\Firmesrbije\parsByLink\VacuumingFirmesrbijeCommand());

/** https://www.pttimenik.com */
$application->add(new  Commands\RS\Pttimenik\parsByLink\PttimenikParserCommand());
$application->add(new  Commands\RS\Pttimenik\parsByLink\VacuumingPttimenikCommand());

/** https://poslovnikontakt.com */
$application->add(new  Commands\RS\Poslovnikontakt\profileAndLinks\PoslovnikontaktParserCommand());
$application->add(new  Commands\RS\Poslovnikontakt\profileAndLinks\VacuumingPoslovnikontaktCommand());
$application->add(new  Commands\RS\Poslovnikontakt\profileAndLinks\ProfileLinksCommand());



/** PL */

/** https://www.pkt.pl */
$application->add(new Commands\PL\Ptc\asyncWithProfile\PktParserCommand());
$application->add(new Commands\PL\Ptc\asyncWithProfile\MainContentCommand());
$application->add(new Commands\PL\Ptc\asyncWithProfile\VacuumingProfileCommand());

/** https://panoramafirm.pl */
$application->add(new  Commands\PL\Panorama\profileAndCategories\PanoramaParserCommand());
$application->add(new  Commands\PL\Panorama\profileAndCategories\VacuumingPanoramaCommand());
$application->add(new  Commands\PL\Panorama\profileAndCategories\ProfileLinksCommand());

/** http://www.poslovniadresar.rs */
$application->add(new  Commands\RS\Poslovniadresar\profileAndLinks\PoslovniadresarParserCommand());
$application->add(new  Commands\RS\Poslovniadresar\profileAndLinks\VacuumingPoslovniadresarCommand());
$application->add(new  Commands\RS\Poslovniadresar\profileAndLinks\ProfileLinksCommand());

/** IT */

/** https://www.paginegialle.it */
$application->add(new  Commands\IT\Paginegialle\parsByLink\PaginegialleParserCommand());
$application->add(new  Commands\IT\Paginegialle\parsByLink\VacuumingPaginegialleCommand());


/** GR */

/** https://www.vrisko.gr */
$application->add(new  Commands\GR\Vrisko\parsByCategories\VriskoParserCommand());
$application->add(new  Commands\GR\Vrisko\parsByCategories\VacuumingVriskoCommand());



$application->run();

