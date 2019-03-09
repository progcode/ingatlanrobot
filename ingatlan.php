<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 02. 13.
 * Time: 19:14
 *
 * v0.2.1
 */

error_reporting(0);
ini_set('display_errors', 0);

if(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])):
    die('Please run only from cli');
endif;

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

$links = array(
    'https://ingatlan.jofogas.hu/pest/budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre/haz?max_price=26000000&min_size=60&st=s',
    'https://ingatlan.com/lista/elado+lakas+budapest+csak-kepes+budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre+25-mFt-ig+60-m2-felett+iii-ker',
    'https://ingatlan.com/szukites/elado+haz+csak-kepes+budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre+26-mFt-ig+60-m2-felett',
    'https://ingatlan.com/lista/elado+telek+pest-megye-buda-kornyeke+8-mFt-ig',
    'https://ingatlan.com/lista/elado+haz+balatonszabadi+balatonvilagos+gardony+siofok+szantod+velence+25-mFt-ig',
);

try {
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
    exit(1);
}

echo "Shutdown robot --------–>\n";
exit();