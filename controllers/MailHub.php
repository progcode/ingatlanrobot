<?php

/************************************************************************************
* Software:		MailHub		                  										*
* Version:		1.00                            									*
* Date: 		2013-09-03                     										*
* Author:		Christoph Dyllick-Brenzinger        								*
* Copyright:	(c) 2013 BigToe														*
* Usage:		A PHP class to send mails from websites. Advantage is to have all	*
*				mail texts at one place and an easy way to send them.				*
************************************************************************************/

/**
 * Include Dotenv library to pull config options from .env file.
 */
if(file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::create(__DIR__, '/../env/.env');
    $dotenv->load();
}

$root = $_SERVER['DOCUMENT_ROOT'];

/*******************************************************************************
*                                                                              *
*                               SMTP Variables                                 *
*                                                                              *
*******************************************************************************/

define('SMTP_HOST',     getenv('SMTP_HOST'));
define('SMTP_USERNAME', getenv('SMTP_USER'));
define('SMTP_PASSWORD', getenv('SMTP_PASS'));
define('SMTP_PORT',     getenv('SMTP_PORT'));                // 25 = no secure connection, 465 = ssl
define('SMTP_SECURE',   getenv('SMTP_SEC'));                   // leave empty if no secure connection, otherwise use 'ssl'


/*******************************************************************************
*                                                                              *
*                               Mail texts	                                   *
*                                                                              *
*******************************************************************************/


$default_template = file_get_contents(dirname(__FILE__) . '/../views/email_templates/default.html');
//$default_template = file_get_contents('default.html');
define ("HTML_DEFAULT_TEMPLATE", "$default_template");

/*******************************************************************************
*                                                                              *
*                               MailHub class                                  *
*                                                                              *
*******************************************************************************/	
	
class MailHub{
	private $text	 		= 'IngatlanRobot';
	private $subject 		= 'IngatlanRobot Mailhub';
	private $MailType		= 'plain';
	private $from			= array();
	private $to				= array();
	private $cc				= array();
	private $bcc			    = array();
	private $log			    = '';
	private $send			= 0;
	private $attachments 	= '';
    private $att_count      = 0;
	
	public function setTo($to, $name = false){
		if($this->validMail($to) == true){
			$this->to[$to] = $name;
			$this->log .= "Success: setTo \"". $to ."\" added;";
			return true;
		}
		else{
			$this->log .= "Error: to mail is wrong;";
			return false;
		}
	}
	
	public function setCC($mail, $name = false){
		if($this->validMail($mail) == true){
			$this->cc[$mail] = $name;
			$this->log .= "Success: setCC \"". $mail ."\" added;";
			return true;
		}
		else{
			$this->log .= "Error: cc mail is wrong;";
			return false;
		}
	}
	
	public function setBCC($mail, $name = false){
		if($this->validMail($mail) == true){
			$this->bcc[$mail] = $name;
			$this->log .= "Success: setBCC \"". $mail ."\" added;";
			return true;
		}
		else{
			$this->log .= "Error: bcc mail is wrong;";
			return false;
		}
	}
	
	public function setFrom($mail, $name = false){
		if($this->validMail($mail) == true){
			$this->from = array();		// only one from allowed
			$this->from[$mail] = $name;
			$this->log .= "Success: setFrom \"". $mail ."\" added;";
			return true;
		}
		else{
			$this->log .= "Error: setFrom email is wrong;";
			return false;
		}
	}
	
	public function setBody($textname){
		$this->text = $textname;
		$this->log .= "Success: email text updated with \"". substr($textname, 0, 20) ."...\";;";
		return true;
	}
	
	public function replacePlaceholders($array){
		$i = 0;
		foreach( $array as $key => $value ) {	
			$this->text = preg_replace("/#". $key ."#/", $value, $this->text);
			$i++;
        }
		$this->log .= "Success: ". $i ." words were replaced;";
		return true;
	}
	
	public function setSubject($subject){
		$this->subject = $subject;
		$this->log .= "Success: Subject set;";
		return true;
	}
	
	public function setMailType($type){
		$type = strtolower($type);
		if($type == 'html' OR $type == 'plain'){
			$this->MailType = $type;
			$this->log .= "Success: mail type changed to ". $type .";";
			return true;
		}
		else{
			$this->log .= "Error: mail type is wrong;";
			return false;
		}	
	}
	
	public function addAttachment($path){
		$name = "";

		// file on server (relative path)
		if(file_exists($path)){
			$name = basename($path);
            $tmp_name = $name;
            $this->log .= "Success: file was found on path ". $path .";";
		}
		// input file name entry...
		elseif(isset($_FILES[$path]['name']) AND $_FILES[$path]['tmp_name'] != ""){
			$name = $_FILES[$path]['name'];
            $tmp_name = $_FILES[$path]['tmp_name'];
            $this->log .= "Success: file was submited via form with name ". $name .";";
		}
		else{
			$this->log .= "Error: no file found for \"". $path ."\";";
			return false;
		}
		
		if($name != ""){
			if(function_exists("mime_content_type")){
				$type = mime_content_type($name);
			}
			else {
				$type = "application/octet-stream";
			}
			$this->attachments[$this->att_count][] = array(
				"name" =>$name, 
                "tmp_name" => $tmp_name,
				"size"=>filesize($name), 
				"type"=>$type,
				"data"=>implode("",file($name))
			);
			$this->log .= "Success: file ". $name ." added;";
            $this->att_count++;
			return true;
		}
	}
	
	public function sendMail(){
		if($this->MailType == "plain"){ $this->sendPlain(); }
		elseif($this->MailType == "html") $this->sendHTML();
		else{
			$this->log .= "Error: predefined mail type is wrong;";
			return false;
		}
	}
	
	public function debug(){
		echo '<style type="text/css">
			table {
				border: collapse;
				padding: 0px;
				margin: 0px;
			}
			th {
				vertical-align: top;
				text-align: left;
				padding: 0 10px 0 0;
				margin: 0px;
				border-bottom: 1px solid #ccc;
			}
			td {
				padding: 0px;
				margin: 0px;
				border-bottom: 1px solid #ccc;
			}
		</style>';
		echo "<h3>Show all debugging information for the MailHub_class.php:</h3>";
		echo "<table>
			<tr><th>From: </th><td>". $this->replaceBrackets($this->showArray('from')) ."</td><tr/>
			<tr><th>To: </th><td>". $this->replaceBrackets($this->showArray('to')) ."</td><tr/>
			<tr><th>CC: </th><td>". $this->showArray('cc') ."</td><tr/>
			<tr><th>BCC: </th><td>". $this->showArray('bcc') ."</td><tr/>
			<tr><th>Subject: </th><td>". $this->subject ."</td><tr/>
			";
			if($this->MailType == 'html'){
				$filename = 'html_preview.html';
				if(!file_exists($filename)){
					fopen($filename, "w");
				}
				if(is_writable($filename)){
					if(!$handle = fopen($filename, "w")){
						$this->log .= "ERROR: html_preview.html could not be opened;";
						return false;
					}
					if(!fwrite($handle, $this->text)) {
						$this->log .= "ERROR: html mail could not be written to html_preview.html;";
						return false;
                    }
                    $this->log .= "Success: html_preview.html was created;";
					fclose($handle);
				}
				else{
					$this->log .= "ERROR: html_preview.html is not writable;";
				}		
				echo "<tr><th>Body: </th><td><iframe src='html_preview.html' ><p>Your browser does not support iframes and html_preview could not be shown.</p></iframe></td><tr/>";
			}
			else{
				echo "<tr><th>Body: </th><td>". $this->showCode($this->text) ."</td><tr/>";
			}
			echo"
			<tr><th>LOG: </th><td>". str_replace(';', '<br/>', $this->log) ."</td><tr/>
			</table>
			";
		
	}
	
	public function send(){
		if($this->send == 1){	return true; }
		else{					return false; }
	}
	
	public function showAllPublicFunctions(){
		$output = "<style>.var_type { color: blue; }</style>";
		$output .= "<h3 style='clear: both;'>Public functions of php class 'MailHub'</h3>\n";
		$output .= "<dl>";
		$output .= "<dt>setTo( <span class='italic'>string \$email [, string \$name = false]</span> )</dt>
			<dd>define a recipient of the mail (Use the function multiple times if you want to define multiple recipients)</dd>";
			
		$output .= "<dt>setCC( <span class='italic'>string \$email [, string \$name = false]</span> )</dt>
			<dd>define a emailadress (with or without a name) that should receive the mail on cc: (Use the function multiple times if you want to define multiple recipients)</dd>";
			
		$output .= "<dt>setBCC( <span class='italic'>string \$email [, string \$name = false]</span> )</dt>
			<dd>same like setTo or setCC only for blind carbon copy (BCC)</dd>";
			
		$output .= "<dt>setFrom( <span class='italic'>string \$email [, string \$name = false]</span> )</dt>
			<dd>define the email (and the name) from the sender of the mail.</dd>";
		
		$output .= "<dt>setBody( <span class='italic'>string \$body</span> )</dt>
			<dd>define/load the body of the email.</dd>";
		
		$output .= "<dt>setSubject( <span class='italic'>string \$subject</span> )</dt>
			<dd>define/load the subject of the email.</dd>";
		
		$output .= "<dt>setMailType( <span class='italic'>string \$type</span> )</dt>
			<dd>choose between plain or html email type. If you choose html type you have to define the SMTP values in the mailhub_class.php. Default value is plain.</dd>";
				
		$output .= "<dt>replacePlaceholders( <span class='italic'>array \$replacements</span> )</dt>
			<dd>This function could be used to replace placeholders in the mail body. Placeholders are always text snippets surounded with an opening # and an ending # (like #surname#). See the examples to get a feeling of the usage.</dd>";			
			
		$output .= "<dt>addAttachment( <span class='italic'>string \$file</span> )</dt>
			<dd>you can use the addAttachment function in two ways to add an attachment to the email (could be used with plain and html mails):<br/>
			- either you add a relative path like \"logo.png\" or<br/>
			- you enter the name value of an input field (input type='file' name='xxx').</dd>";
		
		$output .= "<dt>debug()</dt>
			<dd>use debug to get further information or to search for errors.</dd>";
		
		$output .= "<dt>sendMail()</dt>
			<dd>this function sends the mails.</dd>";
		
		$output .= "<dt>send()</dt>
			<dd>use this function to check if the mails were send out correctly. send() will returns true or false and you can print a success or failure message based on that information.</dd>";
		
		$output = str_replace(array("<dt>", "string ", "array ", "int "), array(
			"\n<dt>", 
			"<span class='var_type'>string </span>", 
			"<span class='var_type'>array </span>", 
			"<span class='var_type'>int </span>"
		), $output);
		
		$output .= "</dl>";
		echo $output;
	}
	
	
	/********************************
	** private functions *
	********************************/
	
	private function sendPlain(){
		$this->prepareText($this->MailType);
		$headers = "Reply-To: ". $this->showArray('from') ." \r\n";
		$headers .= "From: ". $this->showArray('from') ." \r\n";
		$headers .= "CC: ". $this->showArray('cc') ." \r\n";
		$headers .= "BCC: ". $this->showArray('bcc') ." \r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		if(is_array($this->attachments) AND count($this->attachments)){
			$mime_boundary = "-----=" . md5(uniqid(mt_rand(), 1));
			$headers.= "Content-Type: multipart/mixed;\r\n";
			$headers.= " boundary=\"".$mime_boundary."\"\r\n";
			
			$content = "This is a multi-part message in MIME format.\r\n\r\n";
			$content.= "--".$mime_boundary."\r\n";
			$content.= "Content-Type: text/html charset=\"iso-8859-1\"\r\n";
			$content.= "Content-Transfer-Encoding: 8bit\r\n\r\n";
			$content.= $this->text."\r\n";

			foreach($this->attachments AS $dat){
                $data = chunk_split(base64_encode($dat[0]['data']));
                $content.= "--".$mime_boundary."\r\n";
                $content.= "Content-Disposition: attachment;\r\n";
                $content.= "\tfilename=\"".$dat[0]['name']."\";\r\n";
                $content.= "Content-Length: .".$dat[0]['size'].";\r\n";
                $content.= "Content-Type: ".$dat[0]['type']."; name=\"".$dat[0]['name']."\"\r\n";
                $content.= "Content-Transfer-Encoding: base64\r\n\r\n";
                $content.= $data."\r\n";
			}
			$this->text = $content;
						//$content .= "--".$mime_boundary."--"; 
						//}
		}		
		else {
			$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
		}
		$headers .= "Content-Transfer-Encoding: 7bit\n";
		$headers .= "X-Mailer: php\n";
		
		$ok = mail($this->showArray('to', ','),$this->subject,$this->text,$headers);
		if (!$ok){
			$this->log .= "Error: Mail notification was not possible;";
			return false;
		}
		else{
			$this->log .= "Success: Mail was send successful;";
			$this->send = 1;
			return true;
		}
	}
	
	private function validMail($email){
		$pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
		if(preg_match($pattern, $email)){
			return true;
		}
		else{
			return false;
		}
	}
	
	private function showCode($text){
		if($this->MailType != 'html'){
			$text = str_replace('<', '&lt;', $text);
			$text = str_replace('>', '&gt;', $text);
			$text = str_replace('"', '&quot;', $text);
			$text = str_replace('&', '&amp;', $text);
			$text = str_replace('
', '<br/>', $text);
		}
		return $text;
	}
	
	private function replaceBrackets($text){
		$text = str_replace('<', '(', $text);
		$text = str_replace('>', ')', $text);
		$text = str_replace(',', '<br/>', $text);
		return $text;
	}
	
	private function showArray($array_name, $return = 'mail'){       // return = mail: xxx <yyy>, key = xxx, value = yyy
		$output = "";
		if($array_name == 'to' OR $array_name == 'cc' OR $array_name == 'bcc' OR $array_name == 'from'){
			$keys = array_keys($this->$array_name);
			for($i = 0; $i < count($keys); $i++){
				$tmp = $this->$array_name;
				if($tmp[$keys[$i]] != ""){
                    if($return == "key"){ $output .= $tmp[$keys[$i]]; }
                    elseif($return == "value"){ $output .= $keys[$i]; }
                    else{ $output .= $tmp[$keys[$i]] ." <". $keys[$i] .">,"; }
				}
				else{
					$output .= $keys[$i] .",";
				}
			}
        }
		return substr($output, 0, -1);
	}
	
	private function sendHTML(){
		$this->prepareText($this->MailType);		
		include_once (dirname(__FILE__) . '/../lib/mailhub/class.phpmailer.php');
		$mail = new PHPMailer(true);        				// true for throwing Exception on Problems
		$mail->IsSMTP();
		$mail->IsHTML(true);                                // Als HTML-Mail senden
		$mail->SMTPAuth = true;
        $mail->SMTPSecure = SMTP_SECURE;
		$mail->set('Host', 		SMTP_HOST);
		$mail->set('Username', 	SMTP_USERNAME);
		$mail->set('Password', 	SMTP_PASSWORD);
		$mail->set('Port', 		SMTP_PORT);
		$mail->set('Body', 		$this->text);               // HTML Nachricht setzen
		$mail->set('AltBody', 	'plain');              		// Text Nachricht setzen
		$mail->set('CharSet', 	'utf-8');            		// Charset festlegen
		$mail->set('Subject', 	$this->subject); 			// Betreff setzen
        foreach( $this->from as $key => $value ){
             $mail->SetFrom($key, $value);			// Absender setzen
        }
           
		foreach( $this->to as $key => $value ) {	
			$mail->AddAddress($key, $value);      			// add recipient
		}
		foreach( $this->cc as $key => $value ) {	
			$mail->AddCC($key, $value);    
		}
		foreach( $this->bcc as $key => $value ) {	
			$mail->AddBCC($key, $value);   
		}
		
		if(is_array($this->attachments) AND count($this->attachments)){
			foreach($this->attachments AS $dat){
				$mail->AddAttachment($dat[0]['tmp_name'], $dat[0]['name']);
			}
		}
		
		$this->send = 1;
		$mail->Send();
		return true;
	}
	
	private function prepareText($MailType = 'plain'){
		if($MailType == 'html'){
			// remove to much spaces.
			$this->text = trim(preg_replace("/\s+/", " ", $this->text));
		}
		else {
			// nothing to do with plain
		}
	}
}
?>
