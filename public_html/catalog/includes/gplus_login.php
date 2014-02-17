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
require_once 'includes/google/src/apiClient.php';
require_once 'includes/google/src/contrib/apiOauth2Service.php';

//session_start();

$client = new apiClient();

// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
// $client->setClientId('insert_your_oauth2_client_id');
// $client->setClientSecret('insert_your_oauth2_client_secret');
// $client->setRedirectUri('insert_your_oauth2_redirect_uri');
// $client->setDeveloperKey('insert_your_developer_key');

//$client->setScopes(array("https://www.googleapis.com/auth/userinfo.email" ));
$oauth2 = new apiOauth2Service($client);   

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['token']);
}

if (isset($_GET['code'])) {
  $client->authenticate();
  tep_session_register('token');
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()) {
   	
  // The access token may have been updated lazily.
  $_SESSION['token'] = $client->getAccessToken();
   header("Location: googleloader.php");
   
} else {
  if($HTTP_GET_VARS["js"]){ //detection for the presence of javascript
			$_SESSION["javascript"] =true;
			
	}	
  $authUrl = $client->createAuthUrl(); // generate the url and redirect the browser to it
  header("Location: " . $authUrl);
  exit();
}
?>