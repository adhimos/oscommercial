<?php

    require('includes/application_top.php');
  
    if (!tep_session_is_registered('customer_id'))
	{
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
	}
  	
	$check_customer_query = tep_db_query("select customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $check_customer = tep_db_fetch_array($check_customer_query);
	$name=$check_customer['customers_firstname']."".$check_customer['customers_lastname'];
	
	//WATCH FOR SQL INJECTION WITH $NAME
	$check_chat_query = tep_db_query("select room_name from " . TABLE_CHAT_ROOM. " where room_owner = '" . $name . "'");
	$not_found=true;
	while ($check_chat=tep_db_fetch_array($check_chat) AND not_found)
	{
		if($check_chat['room_name']=="Private Room ".$name)
		{
			$not_found=false;
		}
	}
	/*
	if($not_found)
	{
		$check_chat_query = tep_db_query("INSERT INTO `".TABLE_CHAT_ROOM."`(`room_name`, `room_owner`) VALUES ('Private Room ".$name."','".$name."')");
	}
	 */ 
  //Chat
  
  require_once "phpfreechat/src/phpfreechat.class.php"; // adjust to your own path
  $params["serverid"] = md5(__FILE__); // used to identify the chat
  $params["title"]="osCommerce Chat Room";
  $params["channels"]=array("Common Room","Private Room ".$name);
  $params["nick"]=$name;
  $params['admins'] = array('arnauddhimolea'  => 'dhimos',
                            'boby' => 'bobypw');
  $params["frozen_nick"]=true;
  /*
  $params["container_type"] = "mysql";
  $params["container_cfg_mysql_host"] = "localhost";
  $params["container_cfg_mysql_port"] = 3306; 
  $params["container_cfg_mysql_database"] = "osCommerce"; 
  $params["container_cfg_mysql_table"] = "chat"; 
  $params["container_cfg_mysql_username"] = "root"; 
  $params["container_cfg_mysql_password"] = "student"; 
   
   */
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