<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 03. 01.
 * Time: 19:30
 *
 * v0.2.1
 */

require('DbController.php');
require('MailHub.php');

Class Property {

    /**
     * Property constructor.
     */
    public function __construct()
    {
        $this->db = new DbController('admin_ingatlan', 'admin_ingatlan', 'hC9PpKQzzN');
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
     * @param $args
     */
    public function scrapSite($link, $args) {
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
            // = $property_xpath->query('//a[contains(@class,"listing__link")]');

            if($domain == 'ingatlan.jofogas.hu') {
                $property_row = $property_xpath->query('//a[contains(@class,"subject")]');
            } else {
                $property_row = $property_xpath->query('//a[contains(@class,"listing__link")]');
            }

            if($property_row->length > 0){
                foreach($property_row as $row){
                    $title = $row->nodeValue;

                    if($args == 'ingatlan.jofogas.hu') {
                        $url = $row->getAttribute('href');
                    } else {
                        $url = 'https://ingatlan.com'.$row->getAttribute('href');
                    }
                    $hash = md5($row->getAttribute('href'));

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
                            'title' => $title,
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
        $now = date('Y-m-d H:i:s');
        $results = $this->db->get_results( "SELECT * FROM property WHERE synced = '$now'" );

        if($results) {
            $template = 'Új ingatlanokat találtam a megadott keresési feltételek alapján! Az ingatlanok megtekintéséhez kattints ide: https://ingatlan.wpapi.ws/List.php';

            $this->mh->setBody($template);
            $this->mh->setSubject("IngatlanRobot - Új ingatlanok ($sentTime)!");
            $this->mh->setFrom("ingatlanrobot@ingatlanrobot.ai", "IngatlanRobot");
            $this->mh->setTo('kovacsdanielakos@gmail.com');
            $this->mh->setCC('v.meryen@gmail.com');

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