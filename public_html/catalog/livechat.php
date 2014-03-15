<?php

    require('includes/application_top.php');
  
    if (!tep_session_is_registered('customer_id'))
	{
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
	}
  	
	$check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $check_customer = tep_db_fetch_array($check_customer_query);
	$name=$check_customer['customers_firstname']."".$check_customer['customers_lastname'].$check_customer['customers_id'];
	$name=mysql_real_escape_string($name);
	

  //Chat
  
  require_once "phpfreechat/src/phpfreechat.class.php"; // adjust to your own path
  $params["serverid"] = md5(__FILE__); // used to identify the chat
  $params["title"]="osCommerce Chat Room";
  $params["channels"]=array("Common Room");
  $params["max_nick_len"] = 30;
  $params["nick"]=$name;
  $params["theme"]="default";
  $params['admins'] = array('boby' => 'bobypw');
  $params["frozen_nick"]=true;

   
  
 $chat = new phpFreeChat($params);
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
  if ($session_started == false) {
    tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_LOGIN);

      
    
   
  


  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="contentContainer">
  <div class="contentText">
   <?php $chat->printChat(); ?>
  </div>


</div>



<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>