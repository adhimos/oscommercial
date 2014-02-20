<?php
require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);

tep_upload_plugin();

tep_redirect(tep_href_link(FILENAME_DASHBORD, '', 'SSL'));
//tep_initialize_plugins();
//tep_print_plugins();
//echo "</br>Activate Plugins</br>";
//tep_activate_plugin('file1');
?>
