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
  if ($messageStack->size('account') > 0) {
    echo $messageStack->output('account');
  }
?>

<div class="contentContainer">
  <h2><?php echo MY_ACCOUNT_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-person accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-home accountLinkListEntry"></span><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?></li>
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
<div class="ui-widget infoBoxContainer">
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

<?php
} 

include_customer_assistant("<b>Pick an item to buy</b>. 
You can see the new items and items with special prices on the menu bars on either side. You are encourage to browse through the item using the categories list. You can use the menu on left hand side to find product by manufaturer. You can use the quick search to query the available products by any keyword.", 0, 5, 126);
?>


<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

<script type="text/javascript">
  $("#assistentConfirmation").buttonset();
</script>


