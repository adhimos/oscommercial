<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');


   


/*$string="<script>alert('xss')</script>";
echo USE_CACHE;
echo "<br/>";
echo unserialize($_GET['data']);
echo "<br/>";
print urlencode(serialize($string));*/
$url=$HTTP_GET_VARS['goto'];
//echo $url;
			if(strstr($url, "\n")!= false){
				//echo "test";
			}
		if(strstr($url, "\r")!= false){
				//echo "test2";
		}
header('Location:'.$url, TRUE, 302);  
//header('Location: ' . $url);

/*
      if (isset($HTTP_GET_VARS['goto']) && tep_not_null($HTTP_GET_VARS['goto'])) {
        $check_query = tep_db_query("select products_url from " . TABLE_PRODUCTS_DESCRIPTION . " where products_url = '" . tep_db_input($HTTP_GET_VARS['goto']) . "' limit 1");
       echo $HTTP_GET_VARS['goto'];
          tep_redirect('http://' . $HTTP_GET_VARS['goto']);
        
      }

*/


?>

