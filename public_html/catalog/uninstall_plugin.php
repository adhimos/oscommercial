<?php
require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);


tep_remove_plugin();

   tep_redirect(tep_href_link(FILENAME_DASHBORD, '', 'SSL'));

?>
