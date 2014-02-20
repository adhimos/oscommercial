<?php
/*
 $Id$

 osCommerce, Open Source E-Commerce Solutions
 http://www.oscommerce.com

 Copyright (c) 2010 osCommerce

 Released under the GNU General Public License
 */

require ('includes/application_top.php');
require_once 'ext/vcardparse.php';

//Add Account
/*
 set_include_path('/home/student/public_html/catalog/');
 require_once('includes/application_top.php');
 require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);
 */

// Testons si le fichier a bien été envoyé et s'il n'y a pas d'erreur
$MESSAGE="Update the informations of a customer";
if (isset($_FILES['data']) AND $_FILES['data']['error'] == 0) {
	$error = 2;
	// Testons si le fichier n'est pas trop gros
	if ($_FILES['data']['size'] <= 1000000) {
		// Testons si l'extension est autorisée
		$infosfichier = pathinfo($_FILES['data']['name']);
		$extension_upload = $infosfichier['extension'];
		$extensions_autorisees = array('vcf');
		if (in_array($extension_upload, $extensions_autorisees)) {

			// instantiate a parser object
			$parse = new Contact_Vcard_Parse();

			// parse it
			$data = $parse -> fromFile($_FILES['data']['tmp_name']);
			$vcardindex=0;
			$emailcheck=false;
			while (isset($data[$vcardindex]['EMAIL'][0]['value'][0][0])) {

				$email = tep_db_prepare_input(isset($data[$vcardindex]['EMAIL'][0]['value'][0][0]) ? $data[$vcardindex]['EMAIL'][0]['value'][0][0] : '');
				$lastname = tep_db_prepare_input(isset($data[$vcardindex]['N'][0]['value'][0][0]) ? $data[$vcardindex]['N'][0]['value'][0][0] : '');
				$firstname = tep_db_prepare_input(isset($data[$vcardindex]['N'][0]['value'][1][0]) ? $data[$vcardindex]['N'][0]['value'][1][0] : '');
				$street = isset($data[$vcardindex]['ADR'][0]['value'][2][0]) ? $data[$vcardindex]['ADR'][0]['value'][2][0] : '';
				$street2 = isset($data[$vcardindex]['ADR'][0]['value'][2][1]) ? $data[$vcardindex]['ADR'][0]['value'][2][1] : '';
				$cstreet = tep_db_prepare_input($street . " " . $street2);
				$city = tep_db_prepare_input(isset($data[$vcardindex]['ADR'][0]['value'][3][0]) ? $data[$vcardindex]['ADR'][0]['value'][3][0] : '');
				$state = tep_db_prepare_input(isset($data[$vcardindex]['ADR'][0]['value'][4][0]) ? $data[$vcardindex]['ADR'][0]['value'][4][0] : '');
				$postcode = tep_db_prepare_input(isset($data[$vcardindex]['ADR'][0]['value'][5][0]) ? $data[$vcardindex]['ADR'][0]['value'][5][0] : '');
				$country = tep_db_prepare_input(isset($data[$vcardindex]['ADR'][0]['value'][6][0]) ? $data[$vcardindex]['ADR'][0]['value'][6][0] : '');
				$tel = tep_db_prepare_input(isset($data[$vcardindex]['TEL'][0]['value'][0][0]) ? $data[$vcardindex]['TEL'][0]['value'][0][0] : '');
				$bday = tep_db_prepare_input(isset($data[$vcardindex]['BDAY'][0]['value'][0][0]) ? $data[$vcardindex]['BDAY'][0]['value'][0][0] : '');
				
				$email = mysql_real_escape_string($email);
				$lastname = mysql_real_escape_string($lastname);
				$firstname = mysql_real_escape_string($firstname);
				$cstreet = mysql_real_escape_string($cstreet);
				$city = mysql_real_escape_string($city);
				$state = mysql_real_escape_string($state);
				$postcode = mysql_real_escape_string($postcode);
				$country = mysql_real_escape_string($country);
				$tel = mysql_real_escape_string($tel);
				$bday = mysql_real_escape_string($bday);
				
				$check_email_query = tep_db_query("select count(*) as total, customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $email . "'");
				$check_email = tep_db_fetch_array($check_email_query);

				if ($check_email['total'] == 0) {
					$errocheckemail=true;
					break;
					
					
				} else {
					$sql_data_array = array();
					if ($lastname != '') {
						$sql_data_array['customers_lastname'] = $lastname;
					}
					if ($firstname != '') {
						$sql_data_array['customers_firstname'] = $firstname;
					}
					if ($tel != '') {
						$sql_data_array['customers_telephone'] = $tel;
					}
					if (@checkdate(substr($bday, 4, 2), substr($bday, 6, 2), substr($bday, 0, 4))) {
						$sql_data_array['customers_dob'] = $bday;
					}

					tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', " customers_id = '" . (int)$check_email['customers_id'] . "'");

					$sql_data_array = array();


					if ($lastname != '') {
						$sql_data_array['entry_lastname'] = $lastname;
					}
					if ($firstname != '') {
						$sql_data_array['entry_firstname'] = $firstname;
					}
					if ($cstreet != '') {
						$sql_data_array['entry_street_address'] = $cstreet;
					}
					if ($postcode != '') {
						$sql_data_array['entry_postcode'] = $postcode;
					}
					if ($city != '') {
						$sql_data_array['entry_city'] = $city;
					}

					//modify to include country
					
					tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', " customers_id = '" . (int)$check_email['customers_id'] . "'");
					
					
					$vcardindex=$vcardindex+1;
				}

				    $MESSAGE=$vcardindex." files uploaded";
				
				if($vcardindex==0 OR $emailcheck){
					$MESSAGE="There is an error in the file please verify";
				}
			}
		}
else{
	$MESSAGE="WRONG EXTENSION";
} 

	}
else{
	$MESSAGE="FILE TOO BIG";
}

}

require (DIR_WS_INCLUDES . 'template_top.php');
?>	
	
	
	<form action="vcardimport.php" method="post"
	 enctype="multipart/form-data">
	 <p>
	 	Vcard import:<br />
	 	<input type="file" name="data" /><br/>
	 	<input type="submit" value="Import" /><br/>
	 </p>
	 
	 </form>
	 
	 <h3><?php echo $MESSAGE;?></h3>
<?php


require (DIR_WS_INCLUDES . 'template_bottom.php');
require (DIR_WS_INCLUDES . 'application_bottom.php');
?>