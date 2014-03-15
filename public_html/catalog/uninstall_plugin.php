<?php
require('includes/application_top.php');
  
  if (!tep_session_is_registered('customer_id')) {
    $redirect_url = FILENAME_LOGIN;
    $navigation->set_snapshot();
    tep_redirect_to_login(tep_href_link($redirect_url, '', 'SSL'));
  } else {
    $redirect_url = FILENAME_DASHBORD;
 }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);

   tep_remove_plugin();

   tep_redirect(tep_href_link($redirect_url, '', 'SSL'));


?>
