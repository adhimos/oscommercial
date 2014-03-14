<?php

class S_User {

    function checkUser($uid, $oauth_provider, $username ,$social_data) 
	{
		
        $query = mysql_query("SELECT customers_id FROM ".TABLE_USERS." WHERE oauth_uid = '$uid' and oauth_provider = '$oauth_provider'") or die(mysql_error());
        $result = mysql_fetch_array($query);
        if (!empty($result)) {
            // User is already present
			return $result;
        } else {
		
	   		
	   		$email_address = tep_db_prepare_input($social_data["email"]);
	   		$check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
      		$check_email = tep_db_fetch_array($check_email_query);
	  		if ($check_email['total'] > 0) { // user has probably already manually registered and in the database already - lets try and validate this and link it
				$get_existing_customer = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_lastname = '" . tep_db_input($social_data["last_name"]) . "' and customers_firstname = '" . tep_db_input($social_data["first_name"]) . "'");
				
				$get_customer = tep_db_fetch_array($get_existing_customer);
				if($get_customer['customers_id'] > 0){ // The found customer validates - lets link him/her to the db
					$query = mysql_query("INSERT INTO ".TABLE_USERS." (customers_id , oauth_provider, oauth_uid, username) VALUES (".(int)$get_customer['customers_id'].",'$oauth_provider', $uid, '".$social_data["name"]."')") or die(mysql_error());
					$query = mysql_query("SELECT customers_id FROM ".TABLE_USERS." WHERE oauth_uid = '$uid' and oauth_provider = '$oauth_provider'")or die(mysql_error());
					$result = mysql_fetch_array($query);
					return $result;
				}else{ // email has a different first or last name attached to it - so dont attach it
					$error_stack["error"][]=SOCIAL_LOGIN_ERROR;
					return $error_stack;
				}
			}else{ // email address not found so lets make a new account
	  			
				$error = false;
				if (ACCOUNT_DOB == 'true') {
					$dob = tep_db_prepare_input($social_data["birthday"]);
					if ((is_numeric(tep_date_raw_social_logins($dob)) == false) || (@checkdate(substr(tep_date_raw_social_logins($dob), 4, 2), substr(tep_date_raw_social_logins($dob), 6, 2), substr(tep_date_raw_social_logins($dob), 0, 4)) == false)) {
						$error = true;
	
						//$messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
						$error_stack["error"][]=ENTRY_DATE_OF_BIRTH_ERROR;
					}
				}
			
				if (ACCOUNT_GENDER == 'true') {
						$gender = tep_db_prepare_input(substr($social_data["gender"],0,1));
					
				}
				
				$firstname = tep_db_prepare_input($social_data["first_name"]);
				$lastname = tep_db_prepare_input($social_data["last_name"]);
				if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
					$error = true;
	
					//$messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
					$error_stack["error"][]=ENTRY_FIRST_NAME_ERROR;
				}
	
				if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
					$error = true;
	
					//$messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
					$error_stack["error"][]=ENTRY_LAST_NAME_ERROR;
				}
				
				if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
					$error = true;
	
					//$messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
					$error_stack["error"][]=ENTRY_EMAIL_ADDRESS_ERROR;
				} elseif (tep_validate_email($email_address) == false) {
					$error = true;
	
					//$messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
					$error_stack["error"][]=ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
				}
                //Create GUID for Customer
                $guid=guid();
                $hash=md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
				if ($error == false) {
      				$sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
							  'customers_telephone' => "default",
                              'customers_authentication' => $hash,
                              'customers_guid' => guid(),
                              'customers_password' => tep_encrypt_password($uid));
					if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      				if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw_social_logins($dob);
					tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
					$customer_id = tep_db_insert_id();
					$sql_data_array = array('customers_id' => $customer_id,
                              'entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => 'default',
                              'entry_postcode' => 'default',
                              'entry_city' => 'default',
                              'entry_country_id' => STORE_COUNTRY); //for now take the stores country

      				if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
					if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = 'default';
					if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = 'default';
					if (ACCOUNT_STATE == 'true') {
						
						  		$sql_data_array['entry_zone_id'] = '0';
						  		$sql_data_array['entry_state'] = '';
						
					 }
				
					tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
				
					$address_id = tep_db_insert_id();
				
					  tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
					tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");		  
					$query = mysql_query("INSERT INTO ".TABLE_USERS." (customers_id , oauth_provider, oauth_uid, username) VALUES (".(int)$customer_id.",'$oauth_provider', $uid, '".$social_data["name"]."')") or die(mysql_error());
					
            		$query = mysql_query("SELECT customers_id FROM ".TABLE_USERS." WHERE oauth_uid = '$uid' and oauth_provider = '$oauth_provider'") or die(mysql_error());
					
            		$result = mysql_fetch_array($query);
					$result["new_account"]["first_name"]=$firstname;
					$result["new_account"]["last_name"]=$lastname;
					$result["new_account"]["email"]=$email_address;
					$result["new_account"]["gender"]=$gender;
			
            		return $result;
				}else{
					return $error_stack;
				}
			}
			
        }
        
    }

    

}

?>