<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 03. 01.
 * Time: 19:30
 */

require('DbController.php');
require('MailHub.php');

Class Property {

    const siteJofogas = 'https://ingatlan.jofogas.hu/pest/budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre/haz?max_price=26000000&min_size=60&st=s';
    const siteIngatlancom = 'https://ingatlan.com/szukites/elado+haz+csak-kepes+budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre+26-mFt-ig+60-m2-felett';
    const siteIngatlancom2 = 'https://ingatlan.com/lista/elado+telek+pest-megye-buda-kornyeke+8-mFt-ig';

    /**
     * Property constructor.
     */
    public function __construct()
    {
        $this->db = new DbController('admin_ingatlan', 'admin_ingatlan', 'hC9PpKQzzN');
        $this->mh = new MailHub();
    }

    /**
     * @param $siteId
     * @param $args
     */
    public function scrapSite($siteId, $args) {
        $html = file_get_contents($siteId);

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $property_doc = new DomDocument();
        $property_doc->loadHTML($html);

        libxml_use_internal_errors(TRUE); //disable libxml errors

        if(!empty($html)){

            $property_doc->loadHTML($html);
            libxml_clear_errors(); //remove errors for yucky html

            $property_xpath = new DOMXPath($property_doc);
            // = $property_xpath->query('//a[contains(@class,"listing__link")]');

            if($args == 'jf') {
                $property_row = $property_xpath->query('//a[contains(@class,"subject")]');
            } else {
                $property_row = $property_xpath->query('//a[contains(@class,"listing__link")]');
            }

            if($property_row->length > 0){
                foreach($property_row as $row){
                    $title = $row->nodeValue;

                    if($args == 'jf') {
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
                            'portal' => $args,
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
        $now = date('Y-m-d H:i');
        $template = 'Új ingatlanokat találtam a megadott keresési feltételek alapján!
                    Az ingatlanok megtekintéséhez kattints ide: https://ingatlan.wpapi.ws/List.php?a=1';

        $this->mh->setBody($template);
        $this->mh->setSubject("IngatlanRobot - Új ingatlanok ($now)!");
        $this->mh->setFrom("ingatlanrobot@ingatlanrobot.ai", "IngatlanRobot");
        $this->mh->setTo('kovacsdanielakos@gmail.com');
        //$this->mh->setCC('v.meryen@gmail.com');

        $this->mh->sendMail();

        if($this->mh->send() == true){
            echo "mailhub email sent --------–>\n";
        } else {
            echo "mailhub email sent error --------–>\n";
        }
    }

    /** List properties */
    public function listProperties() {
        $now = date('Y-m-d');
        return $this->db->get_results( "SELECT * FROM property WHERE synced LIKE '%$now%' ORDER BY id DESC" );
    }

    /** Truncate */
    public function truncate() {
        $this->db->truncate(array( 'property' ));
    }

}