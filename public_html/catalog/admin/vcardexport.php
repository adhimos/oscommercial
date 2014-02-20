<?php
/*
 $Id$

 osCommerce, Open Source E-Commerce Solutions
 http://www.oscommerce.com

 Copyright (c) 2010 osCommerce

 Released under the GNU General Public License
 */

require ('includes/application_top.php');

$MESSAGE="BEGIN";
if(isset($HTTP_GET_VARS['cID'])){
// Testons si le fichier a bien été envoyé et s'il n'y a pas d'erreur
$customers_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

$check_email_query = tep_db_query("select count(*) as total, customers_firstname,customers_lastname,customers_telephone, customers_gender,customers_email_address,customers_dob from " .TABLE_CUSTOMERS. " where customers_id = '" . (int)$customers_id . "'");
$check_email = tep_db_fetch_array($check_email_query);	

if($check_email['total']==0){
$error=true;
$MESSAGE=" ERROR ON EXPORT";
}
else{
	

	
$check_address_query=tep_db_query("select entry_company, entry_street_address,entry_postcode,entry_city,entry_country_id,entry_state from ".TABLE_ADDRESS_BOOK." where customers_id='".(int)$customers_id."'");	
$check_address=tep_db_fetch_array($check_address_query);

$check_country_query=tep_db_query("select countries_name from ".TABLE_COUNTRIES." where countries_id='".$check_address['entry_country_id']."'");
$check_country=tep_db_fetch_array($check_country_query);

$firstname=$check_email['customers_firstname'];
$lastname=$check_email['customers_lastname'];

if($check_email['customers_gender']=='m'){
$gender="Mr.";
}
else{
$gender="Ms."	;
}

    $sub=substr($check_email['customers_dob'], 0,10);
    $year=substr($sub, 0,4);
	$day=substr(substr($sub, 5,5),0,2);
	$month=substr($sub, 8,9);
$bday=$year.$month.$day;
$tel=$check_email['customers_telephone'];

$company=$check_address['entry_company'];
$street=$check_address['entry_street_address'];
$city=$check_address['entry_city'];
$state=$check_address['entry_state'];
$postecode=$check_address['entry_postcode'];
$country=$check_country['countries_name'];

$email=$check_email['customers_email_address'];


$begin="BEGIN:VCARD"."\r\n";
$versionline="VERSION:3.0"."\r\n";
$nameline="N:".$firstname.";".$lastname.";".$gender."\r\n";
$fullnameline="FN:".$firstname." ".$lastname."\r\n";
$bdayline="BDAY:".$bday."\r\n";
$orgline="ORG:".$company."\r\n";
$teline="TEL;TYPE=HOME,VOICE:".$tel."\r\n";
$adrline="ADR;TYPE=HOME:;;".$street.";".$city.";".$state.";".$postecode.";".$country."\r\n";
$emailine="EMAIL;TYPE=PREF,INTERNET:".$email."\r\n";
$end="END:VCARD"."\r\n";

$output=$begin.$versionline.$nameline.$fullnameline.$bdayline.$orgline.$teline.$adrline.$emailine.$end;
$download_dir='downloads';
$card_filename=$firstname."_".$lastname.".vcf";


    $handle = fopen($download_dir . '/' . $card_filename, 'w');
    fputs($handle, $output);
    fclose($handle);   
	$url='http://' . $_SERVER['SERVER_NAME'] . $port . $path_parts["dirname"] . DIR_WS_ADMIN . $download_dir . '/' . $card_filename;
	/*
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'. $card_filename .'";');
	@readfile($url) OR die();
	unlink($url);
	*/
	header("Location: " .$url);
	//unlink($download_dir . '/' . $card_filename);
	
}
}     

?>