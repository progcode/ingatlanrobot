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

/**
 * Include Dotenv library to pull config options from .env file.
 */
if(file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::create(__DIR__, '/../env/.env');
    $dotenv->load();
}

require('DbController.php');
require('MailHub.php');

Class Property {

    /**
     * Property constructor.
     */
    public function __construct()
    {
        $this->db = new DbController(getenv('DB_USERNAME'), getenv('DB_DATABASE'), getenv('DB_PASSWORD'));
        $this->mh = new MailHub();
    }

    /**
     * Get variable from url and escape it
     *
     * @param $get
     * @return bool|string
     */
    public function get($get) {
        if(isset($_GET[$get])) {
            return $q = htmlspecialchars($_GET[$get]);
        }

        return false;
    }

    /**
     * @param $link
     */
    public function scrapSite($link) {
        $html = file_get_contents($link);
        $parse = parse_url($link);
        $domain = $parse['host'];

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $property_doc = new DomDocument();
        $property_doc->loadHTML($html);

        libxml_use_internal_errors(TRUE); //disable libxml errors

        if(!empty($html)){

            $property_doc->loadHTML($html);
            libxml_clear_errors(); //remove errors for yucky html

            $property_xpath = new DOMXPath($property_doc);

            switch ($domain)
            {
                case 'ingatlan.com':
                    $property_row = $property_xpath->query('//a[contains(@class,"listing__link")]');
                    break;
                case 'ingatlan.jofogas.hu':
                    $property_row = $property_xpath->query('//a[contains(@class,"subject")]');
                    break;
                case 'koltozzbe.hu':
                    $property_row = $property_xpath->query('//a[contains(@class,"listing-link")]');
                    break;
                default:
                    $property_row = $property_xpath->query('//a[contains(@class,"listing__link")]');
            }

            if($property_row->length > 0){
                foreach($property_row as $row){
                    $title = $row->nodeValue;

                    switch ($domain)
                    {
                        case 'ingatlan.com':
                            $url = 'https://ingatlan.com'.$row->getAttribute('href');
                            break;
                        case 'ingatlan.jofogas.hu':
                            $url = $row->getAttribute('href');
                            break;
                        case 'koltozzbe.hu':
                            $url = 'https://koltozzbe.hu'.$row->getAttribute('href');
                            break;
                        default:
                            $url = $row->getAttribute('href');
                    }

                    $hash = md5($url);

                    /** @var $checkProperty */
                    $checkProperty = $this->db->get_results( "SELECT * FROM property WHERE hash = '$hash'" );

                    /**
                     * Fix jofogas issue
                     * @var $skip
                     *
                     */
                    $skip = false;
                    if (strpos($title, 'getParamByPreString')) {
                        $skip = true;
                    }

                    if(!$checkProperty && !$skip) {
                        $property_data = array(
                            'portal' => $domain,
                            'title' => 'ingatlan',
                            'url' => $url,
                            'hash' => $hash,
                            'synced' => date('Y-m-d H:i:s')
                        );

                        $this->db->insert( 'property', $property_data );
                    }
                }
            }
        }
    }

    /** Send notification mail */
    public function sendMail() {
        $sentTime = date('Y-m-d H:i');
        $now = date('Y-m-d H');

        $results = $this->db->get_results( "SELECT * FROM property WHERE synced LIKE '%$now%'" );

        if($results) {
            $this->mh->setBody(HTML_DEFAULT_TEMPLATE);
            $this->mh->setSubject("Mikrobi - Új ingatlanok ($sentTime)!");
            $this->mh->setFrom("ingatlanrobot@ingatlanrobot.ai", "Mikrobi");
            $this->mh->setTo(getenv('USER_EMAIL'));
            $this->mh->setCC(getenv('USER_EMAIL_CC'));

            $this->mh->replacePlaceholders( array(
                "links" => getenv('APP_URL')."/List.php"
            ));

            $this->mh->sendMail();

            if($this->mh->send() == true){
                echo "mailhub email sent --------–>\n";
            } else {
                echo "mailhub email sent error --------–>\n";
            }
        }

        else {
            echo "not found new properties --------–>\n";
        }
    }

    /**
     * List properties
     *
     * @param $getSite
     * @return array
     */
    public function listProperties($getSite) {
        switch ($getSite)
        {
            case 'all':
                $portal = false;
                break;
            case 'icom':
                $portal = 'ingatlan.com';
                break;
            case 'koltozzbe':
                $portal = 'koltozzbe.hu';
                break;
            case 'jf':
                $portal = 'ingatlan.jofogas.hu';
                break;
            default:
                $portal = false;
        }

        $now = date('Y-m-d');

        if($portal) {
            $result = $this->db->get_results( "SELECT * FROM property WHERE portal = '$portal' AND synced LIKE '%$now%' ORDER BY id DESC" );
        } else {
            $result = $this->db->get_results( "SELECT * FROM property WHERE synced LIKE '%$now%' ORDER BY id DESC" );
        }

        return $result;
    }

    /** Truncate */
    public function truncate() {
        $this->db->truncate(array( 'property' ));
    }

}
