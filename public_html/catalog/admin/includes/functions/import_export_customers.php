<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/19/14
 * Time: 1:44 AM
 */
require_once('vcardparse.php');
require_once('password_funcs.php');
$customers_columns = "customers_id,customers_gender,customers_firstname,customers_lastname,customers_dob,customers_email_address,customers_telephone,customers_fax,customers_password,customers_newsletter,customers_authentication,customers_guid,customers_verified";

$address_book_columns = "address_book_id,entry_gender,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_suburb,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";

function escape_csv_value($value) {
    $value = str_replace('"', '""', $value); // First off escape all " and make them ""
    if(preg_match('/,/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value)) { // Check if I have any commas or new lines
        return '"'.$value.'"'; // If I have new lines or commas escape them
    } else {
        return $value; // If no new lines or commas just return the value
    }
}

function exportCustomers($exportCtype)
{
    global  $address_book_columns, $customers_columns;
    $customers_columns_array = explode(',', $customers_columns);
    $address_columns_array = explode(',', $address_book_columns);
    $export_customer_table_query = tep_db_query("SELECT ".$customers_columns." FROM " . TABLE_CUSTOMERS );
    $output = "";
    if($exportCtype == 'csv'){
        $output = $customers_columns.",".$address_book_columns."\n";
        $export_customer_table_query = tep_db_query("SELECT ".TABLE_CUSTOMERS.".".$customers_columns.",".$address_book_columns." FROM " . TABLE_CUSTOMERS." INNER JOIN ".TABLE_ADDRESS_BOOK." ON address_book_id = customers_default_address_id");
        while ($customer = tep_db_fetch_array($export_customer_table_query)) {
            for ($i = 0; $i < count($customers_columns_array); $i++) {
                $output .=escape_csv_value($customer[$customers_columns_array[$i]]).',';
            }
            for ($i = 0; $i < count($address_columns_array); $i++) {
                $output .=escape_csv_value($customer[$address_columns_array[$i]]).',';
            }
            $output .="\n";
        }

        // Download the file

        $filename = "customers.csv";
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
    }
    else if($exportCtype == 'xml') {

        // Start XML file, create parent node
        // Set the content type to be XML, so that the browser will   recognise it as XML.
        header( "content-type: application/xml; charset=ISO-8859-15" );

        // "Create" the document.
        $doc = new DOMDocument( "1.0", "ISO-8859-15" );

        $node = $doc->createElement("customers");
        $parnode = $doc->appendChild($node);

        // Iterate through the rows, adding XML nodes for each
        while ($customer = tep_db_fetch_array($export_customer_table_query)){
            // ADD TO XML DOCUMENT NODE

            $node = $doc->createElement("customer");
            $newnode = $parnode->appendChild($node);
            for ($i = 0; $i < count($customers_columns_array); $i++) {

                $newnode->setAttribute($customers_columns_array[$i], $customer[$customers_columns_array[$i]]);
            }

            $export_address_table_query = tep_db_query("SELECT ".$address_book_columns." FROM " . TABLE_ADDRESS_BOOK . " WHERE customers_id=".$customer['customers_id']);
            $node = $doc->createElement("addresses");
            $addressnode = $newnode->appendChild($node);
            // Iterate through the rows, adding XML nodes for each
            while ($address = tep_db_fetch_array($export_address_table_query)){
                $node = $doc->createElement("address");
                $newnode = $addressnode->appendChild($node);
                for ($i = 0; $i < count($address_columns_array); $i++) {
                    $newnode->setAttribute($address_columns_array[$i], $address[$address_columns_array[$i]]);
                }
            }

        }

        $output = $doc->saveXML();


        $filename = "customers.xml";

        header('Content-Disposition: attachment; filename='.$filename);
    }
    else if($exportCtype == 'vcf'){
    	if (isset($_GET['cID'])) {
	// Testons si le fichier a bien été envoyé et s'il n'y a pas d'erreur
	$customers_id = tep_db_prepare_input($_GET['cID']);

	$check_email_query = tep_db_query("select count(*) as total, customers_firstname,customers_lastname,customers_telephone, customers_gender,customers_email_address,customers_dob from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customers_id . "'");
	$check_email = tep_db_fetch_array($check_email_query);

	if ($check_email['total'] == 0) {
		$output="No such email";
		
	} else {

		$check_address_query = tep_db_query("select entry_company, entry_street_address,entry_postcode,entry_city,entry_country_id,entry_state from " . TABLE_ADDRESS_BOOK . " where customers_id='" . (int)$customers_id . "'");
		$check_address = tep_db_fetch_array($check_address_query);

		$check_country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id='" . $check_address['entry_country_id'] . "'");
		$check_country = tep_db_fetch_array($check_country_query);

		$firstname = $check_email['customers_firstname'];
		$lastname = $check_email['customers_lastname'];

		if ($check_email['customers_gender'] == 'm') {
			$gender = "Mr.";
		} else {
			$gender = "Ms.";
		}

		$sub = substr($check_email['customers_dob'], 0, 10);
		$year = substr($sub, 0, 4);
		$day = substr($sub,8,2);
		$month = substr($sub, 5, 2);
		$bday = $year . $month . $day;
		$tel = $check_email['customers_telephone'];

		$company = $check_address['entry_company'];
		$street = $check_address['entry_street_address'];
		$city = $check_address['entry_city'];
		$state = $check_address['entry_state'];
		$postecode = $check_address['entry_postcode'];
		$country = $check_country['countries_name'];

		$email = $check_email['customers_email_address'];

		$begin = "BEGIN:VCARD" . "\r\n";
		$versionline = "VERSION:3.0" . "\r\n";
		$nameline = "N:" . $firstname . ";" . $lastname . ";" . $gender . "\r\n";
		$fullnameline = "FN:" . $firstname . " " . $lastname . "\r\n";
		$bdayline = "BDAY:" . $bday . "\r\n";
		$orgline = "ORG:" . $company . "\r\n";
		$teline = "TEL;TYPE=HOME,VOICE:" . $tel . "\r\n";
		$adrline = "ADR;TYPE=HOME:;;" . $street . ";" . $city . ";" . $state . ";" . $postecode . ";" . $country . "\r\n";
		$emailine = "EMAIL;TYPE=PREF,INTERNET:" . $email . "\r\n";
		$end = "END:VCARD" . "\r\n";

		$output .= $begin . $versionline . $nameline . $fullnameline . $bdayline . $orgline . $teline . $adrline . $emailine . $end;

		
		$filename = "customers.vcf";
		header('Content-type: text/x-vcard');
        header('Content-Disposition: attachment; filename='.$filename);
	}
	}
	else
	{	
    $check_email_query = tep_db_query("select customers_id, customers_firstname,customers_lastname,customers_telephone, customers_gender,customers_email_address,customers_dob from " . TABLE_CUSTOMERS);
	
	
	while($check_email = tep_db_fetch_array($check_email_query))
	{
		$check_address_query = tep_db_query("select entry_company, entry_street_address,entry_postcode,entry_city,entry_country_id,entry_state from " . TABLE_ADDRESS_BOOK . " where customers_id='" . $check_email['customers_id'] . "'");
		$check_address = tep_db_fetch_array($check_address_query);

		$check_country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id='" . $check_address['entry_country_id'] . "'");
		$check_country = tep_db_fetch_array($check_country_query);

		$firstname = $check_email['customers_firstname'];
		$lastname = $check_email['customers_lastname'];

		if ($check_email['customers_gender'] == 'm') {
			$gender = "Mr.";
		} else {
			$gender = "Ms.";
		}

		$sub = substr($check_email['customers_dob'], 0, 10);
		$year = substr($sub, 0, 4);
		$day = substr($sub,8,2);
		$month = substr($sub, 5, 2);
		$bday = $year . $month . $day;
		$tel = $check_email['customers_telephone'];

		$company = $check_address['entry_company'];
		$street = $check_address['entry_street_address'];
		$city = $check_address['entry_city'];
		$state = $check_address['entry_state'];
		$postecode = $check_address['entry_postcode'];
		$country = $check_country['countries_name'];

		$email = $check_email['customers_email_address'];

		$begin = "BEGIN:VCARD" . "\r\n";
		$versionline = "VERSION:3.0" . "\r\n";
		$nameline = "N:" . $firstname . ";" . $lastname . ";" . $gender . "\r\n";
		$fullnameline = "FN:" . $firstname . " " . $lastname . "\r\n";
		$bdayline = "BDAY:" . $bday . "\r\n";
		$orgline = "ORG:" . $company . "\r\n";
		$teline = "TEL;TYPE=HOME,VOICE:" . $tel . "\r\n";
		$adrline = "ADR;TYPE=HOME:;;" . $street . ";" . $city . ";" . $state . ";" . $postecode . ";" . $country . "\r\n";
		$emailine = "EMAIL;TYPE=PREF,INTERNET:" . $email . "\r\n";
		$end = "END:VCARD" . "\r\n\n";

		$output .= $begin . $versionline . $nameline . $fullnameline . $bdayline . $orgline . $teline . $adrline . $emailine . $end;
		
		$filename = "customers.vcf";
		header('Content-type: text/x-vcard');
        header('Content-Disposition: attachment; filename='.$filename);

		
	}
    }

    echo $output;
    exit;

}
}
function HandleXmlError($errno, $errstr, $errfile, $errline)
{
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
    {
        throw new DOMException($errstr);
    }
    else
        return false;
}

function import_customers_from_XML($content){
    global $customers_columns, $address_book_columns;
    // remove id columns
    $customers_columns = str_replace("customers_id,", "", $customers_columns);
    $address_book_columns = str_replace("address_book_id,", "", $address_book_columns);
    $customers_columns_array = explode(',', $customers_columns);
    $address_columns_array = explode(',', $address_book_columns);

    set_error_handler('HandleXmlError');
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc -> load('customers.xml');
        if (!$xmlDoc->schemaValidate('customers.xsd')) {
            print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
            libxml_display_errors();
        }
    restore_error_handler();

        $x = $xmlDoc->documentElement;


        $sql_data_array = array();
        echo count($customers_columns_array);

        $record = 1;
        foreach ($x->childNodes AS $item)
        {
            if($item->nodeName == '#text')
                continue;
            echo "Reading record #".$record."<br>";


            for ($i = 0; $i < count($customers_columns_array); $i++) {
                $sql_data_array[$customers_columns_array[$i]] =  tep_db_prepare_input($item->getAttribute($customers_columns_array[$i]));
                //echo  $customers_columns_array[$i]."=>". $sql_data_array[$customers_columns_array[$i]]. "<br>";
            }


            $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $sql_data_array["customers_email_address"] . "'");
            $check_email = tep_db_fetch_array($check_email_query);
            if ($check_email['total'] > 0) {
                $error = true;
                echo  $sql_data_array["customers_email_address"]." email address already exists. record #".$record." import failed.<br>";
            }
            else {


                tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
                $customer_id = tep_db_insert_id();

                $sql_data_address = array();

                $addressesNode = $item -> childNodes->item(0);

                foreach ($addressesNode->childNodes AS $address)
                {

                    for ($j = 0; $j < count($address_columns_array); $j++) {
                        $sql_data_address[$address_columns_array[$j]]=  tep_db_prepare_input($address->getAttribute($address_columns_array[$j]));
                        //print  $address_columns_array[$j]."=>". $sql_data_address[$address_columns_array[$j]]. "<br>";
                    }
                }

                $sql_data_address["customers_id"]= $customer_id;
                tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_address);

                $address_id = tep_db_insert_id();
                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "'    where customers_id = '" . (int)$customer_id . "'");


                $customer_info_data = array('customers_info_id'=> $customer_id, 'customers_info_number_of_logons' => '0', 'customers_info_date_account_created' => 'now()',
                    'valid_address'=> '1', 'personal_details_valid' => '1');
                tep_db_perform(TABLE_CUSTOMERS_INFO, $customer_info_data);
                 echo "Successfull imported record #".$record;
            }
            echo "<br>";
            $record++;
        }

}

    function import_customers_by_csv($handle) {
        global  $address_book_columns, $customers_columns;
        // remove id columns
        $customers_columns = str_replace("customers_id,", "", $customers_columns);
        $address_book_columns = str_replace("address_book_id,", "", $address_book_columns);
        $customers_columns_array = explode(',', $customers_columns);

        $address_columns_array = explode(',', $address_book_columns);

                // get headers

                $columns = fgetcsv($handle,100000,",","'");
                $column_list = implode(",",$columns);
                $record = 1;

                //loop through the csv file and insert into database
                while ($data = fgetcsv($handle,100000,",","'")) {

                        for ($i=0;$i<count($columns);$i++){
                            $data[$i]=mysql_real_escape_string($data[$i]);

                        }
                        $sql_data_array = array();
                        for ($i=0;$i<count($customers_columns_array);$i++){
                            $index = array_search($customers_columns_array[$i],$columns );
                            $sql_data_array[$customers_columns_array[$i]] =  $data[$index];
                        }
                        $sql_data_address = array();
                        for ($i=0;$i<count($address_columns_array);$i++){
                            $index = array_search($address_columns_array[$i],$columns );
                            $sql_data_address[$address_columns_array[$i]] =  $data[$index];
                        }

                        //$values = implode(",",$data);


                        $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $sql_data_array["customers_email_address"] . "'");
                        $check_email = tep_db_fetch_array($check_email_query);
                        if ($check_email['total'] > 0) {
                            $error = true;
                            echo  $sql_data_array["customers_email_address"]." email address already exists. record #".$record." import failed.<br>";
                        }
                        else {

                            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
                            $customer_id = tep_db_insert_id();
                            $sql_data_address["customers_id"]= $customer_id;


                            tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_address);
                            $address_id = tep_db_insert_id();
                            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "'    where customers_id = '" . (int)$customer_id . "'");
                            $customer_info_data = array('customers_info_id'=> $customer_id, 'customers_info_number_of_logons' => '0', 'customers_info_date_account_created' => 'now()',
                                'valid_address'=> '1', 'personal_details_valid' => '1');
                            tep_db_perform(TABLE_CUSTOMERS_INFO, $customer_info_data);
                            echo "Successfull imported record #".$record;

                        }
                        echo "<br>";


                        $record +=1;

                }

    }
function import_customers_by_vcard($handle) {
	// instantiate a parser object
	$parse = new Contact_Vcard_Parse();

	// parse it
	$data = $parse -> fromFile($handle);
	$vcardindex = 0;
	$creationindex = 0;
	$updatingindex = 0;
	$emailcheck = false;
	while (isset($data[$vcardindex]['EMAIL'][0]['value'][0][0])) {

		$email = tep_db_prepare_input(isset($data[$vcardindex]['EMAIL'][0]['value'][0][0]) ? $data[$vcardindex]['EMAIL'][0]['value'][0][0] : '');
		$lastname = tep_db_prepare_input(isset($data[$vcardindex]['N'][0]['value'][0][0]) ? $data[$vcardindex]['N'][0]['value'][0][0] : '');
		$firstname = tep_db_prepare_input(isset($data[$vcardindex]['N'][0]['value'][1][0]) ? $data[$vcardindex]['N'][0]['value'][1][0] : '');
		$gender=tep_db_prepare_input(isset($data[$vcardindex]['N'][0]['value'][2][0]) ? $data[$vcardindex]['N'][0]['value'][2][0] : '');
		if($gender=='' OR $gender=="Mr." OR $gender=="Mr"){
			$gender="m";
		}
		else{
			$gender="f";
		}
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
		
		$log=$email."/".$lastname."/".$firstname."/".$cstreet."/".$state."/".$country."/".$tel."/".$bday;
		echo $log;
		echo "<br>";
		$check_email_query = tep_db_query("select count(*) as total, customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $email . "'");
		$check_email = tep_db_fetch_array($check_email_query);

		if ($check_email['total'] == 0) {

			if ($lastname != '' AND $firstname != '' AND $tel != '' AND @checkdate(substr($bday, 4, 2), substr($bday, 6, 2), substr($bday, 0, 4)) AND $cstreet != '' AND $postcode != '' AND $city != '' AND $country != '') {
				
				//echo "1";
				//TABLE CUSTOMER
				$sql_data_array = array();
				$sql_data_array['customers_lastname'] = $lastname;
				$sql_data_array['customers_firstname'] = $firstname;
				$sql_data_array['customers_telephone'] = $tel;
				$sql_data_array['customers_dob'] = $bday;
				
				//HASH PASSWORD
				$sql_data_array['customers_password'] = tep_encrypt_password("123456789");
				
				$sql_data_array['customers_guid'] = guid();
				
				$sql_data_array['customers_gender'] = $gender;
				$sql_data_array['customers_verified'] = '1';
				$sql_data_array['customers_email_address'] = $email;
				//TABLE ADDRESS
				$sql_data_address = array();
				$sql_data_address['entry_lastname'] = $lastname;
				$sql_data_address['entry_firstname'] = $firstname;
				$sql_data_address['entry_street_address'] = $cstreet;
				$sql_data_address['entry_postcode'] = $postcode;
				$sql_data_address['entry_city'] = $city;
				$sql_data_address['entry_gender'] = $gender;
				$check_country_query = tep_db_query("select count(*) as totalc, countries_id from " . TABLE_COUNTRIES . " where LOWER(countries_name)=LOWER('" . $country . "')");
				$check_country = tep_db_fetch_array($check_country_query);
				

				if ($check_country['totalc'] > 0) {

					$sql_data_address['entry_country_id'] = $check_country['countries_id'];

					tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

					$customer_id = tep_db_insert_id();
					$sql_data_address["customers_id"] = $customer_id;

					tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_address);
					$address_id = tep_db_insert_id();
					//TABLE CUSTOMER INFO
					$customer_info_data = array('customers_info_id' => $customer_id, 'customers_info_number_of_logons' => '0', 'customers_info_date_account_created' => 'now()', 'valid_address' => '1', 'personal_details_valid' => '1');
					tep_db_perform(TABLE_CUSTOMERS_INFO, $customer_info_data);

					
					tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "'    where customers_id = '" . (int)$customer_id . "'");
				    
					echo "Successfull imported record #" . $vcardindex;
				}
				else{
					echo "Not enough information to create record (country missing)#".$vcardindex;
				}

			}
			else{
				
				
				echo "Not enough information to create record #".$vcardindex;
			}
			

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
			
			if($country !=''){
						$check_country_query = tep_db_query("select count(*) as totalc, countries_id from " . TABLE_COUNTRIES . " where LOWER(countries_name)=LOWER('" . $country . "')");
						$check_country = tep_db_fetch_array($check_country_query);
						
						if ($check_country['totalc'] > 0) {
							$sql_data_array['entry_country_id'] = $check_country['countries_id'];
						}
						
					}

			

			tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', " customers_id = '" . (int)$check_email['customers_id'] . "'");

			echo "Successfull updated record #" . $vcardindex;
		}
		echo "<br>";
 		$vcardindex++;
	}
}
function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
    }
}
?>