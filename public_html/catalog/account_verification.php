<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT_VERIFICATION);

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  if (sizeof($navigation->snapshot) > 0) {
    $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
    $navigation->clear_snapshot();
  } else {
    $origin_href = tep_href_link(FILENAME_DEFAULT);
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
  
  $error=false;
  

  
  if(isset($_GET['id']) AND isset($_GET['code'])){
  $id_verify=tep_db_prepare_input($_GET['id']);
  $code_verify=tep_db_prepare_input($_GET['code']);
  

  // Check if email exists
    $check_customer_query = tep_db_query("select customers_verified , customers_authentication from " . TABLE_CUSTOMERS . " where customers_guid= '" . tep_db_input($id_verify) . "'");
    
    if (!tep_db_num_rows($check_customer_query)) {
      $error = true;
		
    } else {
      $check_customer = tep_db_fetch_array($check_customer_query);
  
  	 if($check_customer['customers_verified']==1 || $check_customer['customers_authentication']==NULL){
  	 	$error=true;
		
  	 }
	 else{
	 	
		tep_db_query("update " . TABLE_CUSTOMERS . " set customers_authentication=NULL , customers_verified= 1   where customers_guid = '" . tep_db_input($id_verify) . "'");
		
	 }
	}
  }
  else{
	 	$error=true;
	  echo 'error3';
  }
  
  if($error==false){  	
  	
	?>

<h1><?php echo HEADING_TITLE; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <?php echo TEXT_ACCOUNT_VERIFIED ?>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', $origin_href); ?></span>
  </div>
</div>
	<?php
  }
  else{

  
	 
  
  
?>

<h1><?php echo HEADING_TITLE_ERROR; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <?php echo TEXT_ACCOUNT_VERIFIED_ERROR; ?>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', $origin_href); ?></span>
  </div>
</div>

<?php
  }
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>