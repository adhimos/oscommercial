<html>
<body>
<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
 //if($_GET){
	 //$dat= urlencode($_SERVER['QUERY_STRING']);
	 
 //mail('julian@conzept.de', 'My Subject', $dat);
 //}
 

//require(DIR_WS_INCLUDES . 'template_top.php');
require_once 'includes/google/src/apiClient.php';
require_once 'includes/google/src/contrib/apiOauth2Service.php';
//session_start();


require('includes/application_top.php');
require (DIR_WS_FUNCTIONS.'social_login_functions.php');

$client = new apiClient();
//$client->setApplicationName("Google+ PHP Starter Application");
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
// $client->setClientId('insert_your_oauth2_client_id');
// $client->setClientSecret('insert_your_oauth2_client_secret');
// $client->setRedirectUri('insert_your_oauth2_redirect_uri');
// $client->setDeveloperKey('insert_your_developer_key');
//$client->setScopes(array("https://www.googleapis.com/auth/userinfo.email"));

$oauth2 = new apiOauth2Service($client); 

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['token']);
}

if (isset($_GET['code'])) {
  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
 
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()) {
  $user = $oauth2->userinfo->get();
  $user["first_name"]=$user["given_name"];
  $user["last_name"]=$user["family_name"];
  $dom = array("http://", "https://");
  $user["picture"]=str_replace($dom, "", $user["picture"]);
  //$user["birthday"]=substr($user["birthday"], 5, 2)."/".substr($user["birthday"], 8, 2)."/".substr($user["birthday"], 0, 4);  
  $user["birthday"] = "01/01/2000";
  	//echo "<pre>";
	//print_r($user);
	//echo "</pre>";
	
	$g_user = new S_User();
		unset($userdata);
    $userdata = $g_user->checkUser($user['id'], 'google', $user['name'] , $user);
		
		
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
  		  $_SESSION["social_picture"]= $user["picture"];
		  //2.3.2 
		  tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1, password_reset_key = null, password_reset_date = null where customers_info_id = '" . (int)$customer_id . "'");
		  //2.3.1 for 2.3.1 unhide the query below        Hide the query above
		  //tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
		  
		  $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

		  // restore cart contents
		  $cart->restore_contents();
	  	 
		  if($userdata["new_account"]){ //new user so lets send some greetings
			 ery("update " . TABLE_CUSTOMERS_INFO . " set valid_address = 0 , personal_details_valid = 0 where customers_info_id = '" . (int)$customer_id . "'"); // new accounts created from social login wont have full address data 
				
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
					
				  if(isset($_SESSION["javascript"])){ // was the script called with javascript on
						
						
							echo"<script>
									window.opener.location.href = '".tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL')."';
									self.close();
								</script>";
							exit();
					}else{	
				  		tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
					}
			 }
			 else{
			
				 $navigation = $_SESSION['navigation'];
				  if (sizeof($navigation->snapshot) > 0) {
					
					  $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
					  $navigation->clear_snapshot();
					  if(isset($_SESSION["javascript"])){ // was the script called with javascript on
							
							echo"<script>
									window.opener.location.href = '".$origin_href."';
									self.close();
								</script>";
							exit();
						}else{
						  tep_redirect($origin_href);
						}
				  } else {
				  	
					  if(isset($_SESSION["javascript"])){ // was the script called with javascript on
						

							echo"<script>
									window.opener.location.href = '".tep_href_link(FILENAME_DEFAULT)."';
									self.close();
								</script>";
							exit();
						}else{
							tep_redirect(tep_href_link(FILENAME_DEFAULT));
						}
				   }
			}
		}else{
	
			//problem with google details - login to google was successfull but for some reason we cant link it to an existing account 

			tep_session_register('social_login_error');
			
			$_SESSION["social_login_error"] = $userdata;
			if(isset($_SESSION["javascript"])){
				
					echo"<script>
						
							window.opener.location.href = '".tep_href_link(FILENAME_LOGIN, '', 'SSL')."';
							self.close();
						</script>";
					exit();
				}else{
  			tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
				}
		}

  // The access token may have been updated lazily.
  $_SESSION['token'] = $client->getAccessToken();
}
if($_GET["error"]){ // last filter - the user has denied access to their google account infos
	
	tep_session_register('social_login_error');
			
			$_SESSION["social_login_error"]['error'][] = $_GET["error"];
			if(isset($_SESSION["javascript"])){ // was the script called with javascript on
				
					echo"<script>
							window.opener.location.href = '".tep_href_link(FILENAME_LOGIN, '', 'SSL')."';
							self.close();
						</script>";
					exit();
				}else{
  			tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
	}
}
//require(DIR_WS_INCLUDES . 'application_bottom.php');
//require(DIR_WS_INCLUDES . 'template_bottom.php');
?>
</body>
</html>
