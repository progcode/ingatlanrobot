<?php
/**
 * IngatlanRobot
 *
 * PHP Version 7
 *
 * @category  IngatlanRobot
 * @package   Iconocoders
 * @author    Iconocoders <support@icoders.co>
 * @copyright 2017-2019 Iconocoders
 * @license   Apache License 2.0
 * @link      http://iconocoders.com
 */

if(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])):
    die('Please run only from cli');
endif;

/**
 * Include Dotenv library to pull config options from .env file.
 */
if(file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::create(__DIR__, '/env/.env');
    $dotenv->load();
}

if(getenv('APP_DEBUG') == 'false') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

require('controllers/Property.php');
$property = new Property();

echo "Init robot --------–>\n";
$command = $argv[1];

if($command == '--flush') {
    echo "Start scrapping --------–>\n";
    $property->truncate();

    echo "Table truncated --------–>\n";
    echo "Shutdown robot --------–>\n";

    exit();
}

try {

    $links = array(
        'https://ingatlan.com/lista/elado+haz+ar-szerint-csokkeno+alsoors+balatonalmadi+balatonfoldvar+balatonfured+balatonfuzfo+balatonkenese+balatonszabadi+balatonvilagos+csopak+felsoors+siofok+szantod+zamardi+orvenyes+balatonakarattya+25-mFt-ig',
        'https://ingatlan.com/lista/elado+telek+pest-megye-buda-kornyeke+9-mFt-ig+lakoovezeti-telek',
        'https://ingatlan.com/lista/elado+haz+budapest+pest-megye+osszkomfortos+csak-kepes+nem-berleti-jog+30-mFt-ig+81-m2-felett',
        'https://koltozzbe.hu/elado-teglalakas+csaladi_haz+ikerhaz+sorhaz+telek+nyaralo-budapest+dunakeszi+szentendre+pomaz+vac+erdokertes+veresegyhaz+pilisvorosvar+piliscsaba?p2=32000000&a1=70&order=2',
        'https://koltozzbe.hu/elado-teglalakas+csaladi_haz+ikerhaz+sorhaz+telek+nyaralo-siofok+balatonfokajar?p2=25000000&order=2'
    );

    echo "Start scrapping --------–>\n";

    foreach($links as $link) {
        echo "Scrapping link --------–>\n";
        $property->scrapSite($link);
    }

    echo "Sleep 5 seconds before send email --------–>\n";
    sleep(5);

    echo "Send notif emails --------–>\n";
    $property->sendMail();

    echo "End scrapping --------–>\n";

} catch (Exception $e) {
    echo 'Script error: ' . $e->getMessage();
    exit();
}

echo "Shutdown robot --------–>\n";
exit();
