<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 02. 13.
 * Time: 19:14
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

try {
    echo "Start scrapping --------–>\n";

    echo "Scrapping jofogas --------–>\n";
    $property->scrapSite($property::siteJofogas, 'jf');

    echo "Sleep 5 seconds before next scrapping --------–>\n";
    sleep(5);

    echo "Scrapping ingatlancom#1 --------–>\n";
    $property->scrapSite($property::siteIngatlancom, 'icom');

    echo "Sleep 5 seconds before next scrapping --------–>\n";
    sleep(5);

    echo "Scrapping ingatlancom#2 --------–>\n";
    $property->scrapSite($property::siteIngatlancom2, 'icom');

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