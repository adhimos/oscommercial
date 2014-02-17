<?php

require(DIR_WS_CLASSES.'facebook.php');
require (DIR_WS_INCLUDES.'fbconfig.php');
require (DIR_WS_FUNCTIONS.'social_login_functions.php');


$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => APP_SECRET,
  
));

$user = $facebook->getUser();


	
if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
	
    $user_profile = $facebook->api('/me');
	

  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($user) {
	    
        $f_user = new S_User();
		unset($userdata);
		
        $userdata = $f_user->checkUser($user_profile['id'], 'facebook', $user_profile['name'] , $user_profile);
		
        if(isset($userdata["customers_id"])){
			
		
		  // reset session token
		  if (SESSION_RECREATE == 'True') {
			  tep_session_recreate();
		  }
		  
		  
		  $check_customer_query = tep_db_query("select customers_firstname, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$userdata["customers_id"] . "'");
		  $check_customer = tep_db_fetch_array($check_customer_query);
		  
		  $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$userdata["customers_id"] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "'");
		  $check_country = tep_db_fetch_array($check_country_query);
		  
		  $customer_id = $userdata["customers_id"];
		  $customer_default_address_id = $check_customer['customers_default_address_id'];
		  $customer_first_name = $check_customer['customers_firstname'];
		  $customer_country_id = $check_country['entry_country_id'];
		  $customer_zone_id = $check_country['entry_zone_id'];
		  tep_session_register('customer_id');
		  tep_session_register('customer_default_address_id');
		  tep_session_register('customer_first_name');
		  tep_session_register('customer_country_id');
		  tep_session_register('customer_zone_id');
		  tep_session_register('social_picture');
		  
  		  $_SESSION["social_picture"]='graph.facebook.com/' .$user_profile['id'].'/picture';
		  //2.3.2
		  tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1, password_reset_key = null, password_reset_date = null where customers_info_id = '" . (int)$customer_id . "'");
		  //2.3.1      for 2.3.1 unhide the query below        Hide the query above
		  //tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
		  $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

		  // restore cart contents
		  $cart->restore_contents();
	  	
		  if($userdata["new_account"]){
			    
				tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set valid_address = 0 , personal_details_valid = 0 where customers_info_id = '" . (int)$customer_id . "'"); // new account created from social login wont have full address data
				
				require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);
			  	$name = $userdata["new_account"]["first_name"] . ' ' . $userdata["new_account"]["last_name"];

				  if (ACCOUNT_GENDER == 'true') {
					 if ($userdata["new_account"]["gender"] == 'm') {
					   $email_text = sprintf(EMAIL_GREET_MR, $userdata["new_account"]["last_name"]);
					 } else {
					   $email_text = sprintf(EMAIL_GREET_MS, $userdata["new_account"]["last_name"]);
					 }
				  } else {
					$email_text = sprintf(EMAIL_GREET_NONE, $userdata["new_account"]["first_name"]);
				  }
			      
				  $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
				  tep_mail($name, $userdata["new_account"]["email"], EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
					
				  if(isset($_SESSION["javascript"])){
						
						
							echo"<script>
									window.opener.document.location.href = '".tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL')."';
									self.close();
								</script>";
							exit();
					}else{	
				  		tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
					}
			 }else{
		
				  if (sizeof($navigation->snapshot) > 0) {
					  $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
					  $navigation->clear_snapshot();
					  if(isset($_SESSION["javascript"])){
							
							
						
							echo"<script>
									window.opener.document.location.href = '".$origin_href."';
									self.close();
								</script>";
							exit();
						}else{
						  tep_redirect($origin_href);
						}
				  } else {
					  if(isset($_SESSION["javascript"])){
						
							
						
							echo"<script>
									window.opener.document.location.href = '".tep_href_link(FILENAME_DEFAULT)."';
									self.close();
								</script>";
							exit();
						}else{
							tep_redirect(tep_href_link(FILENAME_DEFAULT));
						}
				   }
			}
			
           
		}else{
			//problem with facebook details - login to facebook was successfull but for some reason we cant link it to an existing account 
			
			#echo "<pre>";
			#print_r($facebook_data);
			#print_r($userdata);
			#echo "/<pre>";
			
			tep_session_register('social_login_error');
			
			$_SESSION["social_login_error"] = $userdata;
			if(isset($_SESSION["javascript"])){
				
					
				
					echo"<script>
							window.opener.document.location.href = '".tep_href_link(FILENAME_LOGIN, '', 'SSL')."';
							self.close();
						</script>";
					exit();
				}else{
  			tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
				}
		}
		
  #$logoutUrl = $facebook->getLogoutUrl();
} else {
	if($HTTP_GET_VARS["js"]){ //detection for the presence of javascript
			$_SESSION["javascript"] =true;
			
	}
  //$loginUrl = $facebook->getLoginUrl();
  
  $params = array(
    'scope' => 'email,user_birthday', // we wil request email and age as extras
  	'redirect_uri' => tep_href_link(FILENAME_LOGIN, 'oauth_provider=facebook', 'SSL')
	);
  $loginUrl   = $facebook->getLoginUrl($params); // generate the url and redirect the browser to it
  header("Location: " . $loginUrl);
  exit();
}