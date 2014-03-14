<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<h1><?php echo HEADING_TITLE; ?></h1>
<?php
// social login start

//if(!tep_get_valid_address_status($customer_id) || !tep_get_valid_personal_details($customer_id)){
	//$messageStack->add('account', ENTRY_SOCIAL_LOGIN_COMPLETE_ERROR);	
//}
if(!tep_get_valid_address_status($customer_id)){
	$entry_query = tep_db_query("select entry_company, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)tep_default_address_id($customer_id) . "'");
		
	$entry_fetch = tep_db_fetch_array($entry_query);
	
	$address_errors=0;
	if($entry_fetch["entry_company"] == "default"){
		$address_errors++;
	}
	if($entry_fetch["entry_street_address"] == "default"){
		$address_errors++;
	}
	if($entry_fetch["entry_suburb"] == "default"){
		$address_errors++;
	}
	if($entry_fetch["entry_postcode"] == "default"){
		$address_errors++;
	}
	if($entry_fetch["entry_city"] == "default"){
		$address_errors++;
	}
}
// social login end
?>

<?php
  if ($messageStack->size('account') > 0) {
    echo $messageStack->output('account');
  }
?>

<div class="contentContainer">
  <h2><?php echo MY_ACCOUNT_TITLE; ?></h2>
<div>
<?php
// social login start 
  if($_SESSION["social_picture"]){

  echo '<img width=25 height=25 src="'.(($request_type == 'SSL')?"https://":"http://").$_SESSION["social_picture"].'">';

  }
  // social login stop
 ?>

</div>
  <div class="contentText">
    <ul class="accountLinkList">
      <!--<li><span class="ui-icon ui-icon-person accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-home accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?></li>
     -->
	 <li><span class="ui-icon ui-icon-person accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION .(!tep_get_valid_personal_details($customer_id)?'<span class="messageStackError">(1)</span>':''). '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-home accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK .($address_errors > 0?'<span class="messageStackError">('.$address_errors.')</span>':''). '</a>'; ?></li>
	 <li><span class="ui-icon ui-icon-key accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL') . '">' . MY_ACCOUNT_PASSWORD . '</a>'; ?></li>
    </ul>
  </div>

  <h2><?php echo MY_ORDERS_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-cart accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . MY_ORDERS_VIEW . '</a>'; ?></li>
    </ul>
  </div>

  <h2><?php echo EMAIL_NOTIFICATIONS_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-mail-closed accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-heart accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_PRODUCTS . '</a>'; ?></li>
    </ul>
  </div>
	
  
  <h2><?php echo ADVERTISEMENT_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-image accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_CREATE_ADVERTISEMENT, '', 'SSL') . '">' . ADVERTISEMENT_PLACEMENT . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-suitcase accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ADVERTISEMENT_HISTORY, '', 'SSL') . '">' . ADVERTISEMENT_VIEW . '</a>'; ?></li>
    </ul>
  </div>

</div>

<?php


if (isset($HTTP_GET_VARS['assistant'])){

if($HTTP_GET_VARS['assistant'] == 'true') {
	$assistant = true;
	$_SESSION['assistant'] = true;
} else if ($HTTP_GET_VARS['assistant'] == 'false'){
	$assistant = false;
	$_SESSION['assistant'] = false;
}

tep_session_register(assistant);
}
?>

<?php
if (!tep_session_is_registered('assistant')) {

?>
<div class="ui-widget infoBoxContainer shadow" id="customerAssistantMessageBox">
<div class="ui-widget-header infoBoxHeading">
<?php
echo TITLE_CUSTOMER_ASSITANCE_REQUIRED; ?>
</div>
<div class="ui-widget-content infoBoxContents" id="assistentConfirmation" style="text-align: center;">
<?php
echo IS_CUSTOMER_ASSISTANT_REQUIRED;
echo "<br>";
echo tep_draw_button(CUSTOMER_ASSISTANT_NEEDED, 'needAssistant', tep_href_link(FILENAME_ACCOUNT, 'assistant=true', 'SSL')).
tep_draw_button(CUSTOMER_ASSISTANT_NOT_NEEDED, 'noAssistant', tep_href_link(FILENAME_ACCOUNT, 'assistant=false', 'SSL'));
?>


</div>
</div> 
<script>
function rmsg(e){  
  e.source.postMessage(eval(e.data), e.origin);
}
window.addEventListener("message", rmsg, false);
</script>
<?php
} 

include_customer_assistant("<b>Pick an item to buy</b>. 
You can see the new items and items with special prices on the menu bars on either side. You are encourage to browse through the item using the categories list. You can use the menu on left hand side to find product by manufaturer. You can use the quick search to query the available products by any keyword.", 0, 5, 126);
?>


<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
