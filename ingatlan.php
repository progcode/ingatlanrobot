<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 02. 13.
 * Time: 19:14
 */

require('vendor/autoload.php');
use duzun\hQuery;

/**
 * @var $cache_path
 * @var $cache_expires
 */
hQuery::$cache_path = "cache";
hQuery::$cache_expires = 3600;

$scrape_url = 'https://ingatlan.com/szukites/elado+haz+csak-kepes+budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre+26-mFt-ig+60-m2-felett';
$http_context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'user_agent' => 'iRobot/1.0 +url',
        'proxy' => '95.167.150.28:8080',
        'header' => [],
    ]]);

$htmldoc = hQuery::fromFile( $scrape_url, false, $http_context );
$banners = $htmldoc->find('.listing__card');

echo "<pre>";
var_dump($banners);
echo "<pre>";

exit();